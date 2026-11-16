<?php
declare(strict_types=1);

require_once __DIR__ . '/db.php';

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

/**
 * Pomocnicza funkcja wysyłająca odpowiedź JSON z odpowiednim kodem HTTP.
 */
function sendJson(int $statusCode, array $payload): void
{
    http_response_code($statusCode);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    exit;
}

/**
 * Waliduje i normalizuje format daty i godziny (YYYY-MM-DD HH:MM -> YYYY-MM-DD HH:MM:00).
 */
function parseDateTime(string $value): ?DateTimeImmutable
{
    $value = trim($value);
    $formats = ['Y-m-d H:i', 'Y-m-d H:i:s'];

    foreach ($formats as $format) {
        $dt = DateTimeImmutable::createFromFormat($format, $value);
        if ($dt !== false && $dt->format($format) === $value) {
            return $dt;
        }
    }

    return null;
}

/**
 * Zadanie dodatkowe: Generowanie pliku kalendarza .ics (RFC 5545) dla konkretnej rezerwacji.
 */
function handleDownloadIcs(PDO $pdo, int $reservationId): void
{
    if ($reservationId <= 0) {
        sendJson(400, [
            'status'  => 'error',
            'message' => 'Nieprawidłowy identyfikator rezerwacji do eksportu ICS.'
        ]);
    }

    $stmt = $pdo->prepare(
        'SELECT r.id, r.user_name, r.start_time, r.end_time, res.name AS resource_name, res.type AS resource_type
         FROM reservations r
         INNER JOIN resources res ON r.resource_id = res.id
         WHERE r.id = :id'
    );
    $stmt->execute([':id' => $reservationId]);
    $reservation = $stmt->fetch();

    if (!$reservation) {
        sendJson(404, [
            'status'  => 'error',
            'message' => 'Nie znaleziono wskazanej rezerwacji.'
        ]);
    }

    $startDt = new DateTimeImmutable($reservation['start_time']);
    $endDt   = new DateTimeImmutable($reservation['end_time']);
    $nowUtc  = new DateTimeImmutable('now', new DateTimeZone('UTC'));

    $resourceName = html_entity_decode((string)$reservation['resource_name'], ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $userName     = html_entity_decode((string)$reservation['user_name'], ENT_QUOTES | ENT_HTML5, 'UTF-8');

    $summary = sprintf('Rezerwacja: %s (%s)', $resourceName, $userName);
    $description = sprintf(
        'Rezerwacja zasobu %s w systemie Smart Office SPA. Osoba rezerwująca: %s.',
        $resourceName,
        $userName
    );

    // Escapowanie znaków specjalnych w formacie iCalendar
    $escapeIcs = static function (string $text): string {
        $text = str_replace(['\\', ';', ','], ['\\\\', '\\;', '\\,'], $text);
        return str_replace(["\r\n", "\n", "\r"], '\n', $text);
    };

    $icsLines = [
        'BEGIN:VCALENDAR',
        'VERSION:2.0',
        'PRODID:-//Smart Office SPA//Klasa 4 TI INF.03//PL',
        'CALSCALE:GREGORIAN',
        'METHOD:PUBLISH',
        'BEGIN:VEVENT',
        'UID:smart-office-res-' . $reservation['id'] . '@smartoffice.local',
        'DTSTAMP:' . $nowUtc->format('Ymd\THis\Z'),
        'DTSTART:' . $startDt->format('Ymd\THis'),
        'DTEND:' . $endDt->format('Ymd\THis'),
        'SUMMARY:' . $escapeIcs($summary),
        'DESCRIPTION:' . $escapeIcs($description),
        'LOCATION:' . $escapeIcs('Biuro Smart Office - ' . $resourceName),
        'END:VEVENT',
        'END:VCALENDAR',
    ];

    $icsContent = implode("\r\n", $icsLines) . "\r\n";

    http_response_code(200);
    header('Content-Type: text/calendar; charset=utf-8');
    header('Content-Disposition: attachment; filename="rezerwacja.ics"');
    header('Content-Length: ' . strlen($icsContent));
    echo $icsContent;
    exit;
}

try {
    if ($method === 'GET') {
        // Obsługa eksportu .ics (zadanie dodatkowe)
        if (isset($_GET['action']) && $_GET['action'] === 'ics') {
            $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT) ?: 0;
            handleDownloadIcs($pdo, $id);
        }

        $startRaw = $_GET['start'] ?? '';
        $endRaw   = $_GET['end'] ?? '';

        $startDt = parseDateTime((string)$startRaw);
        $endDt   = parseDateTime((string)$endRaw);

        if (!$startDt || !$endDt) {
            sendJson(400, [
                'status'  => 'error',
                'message' => 'Wymagane poprawne parametry URL: start oraz end w formacie YYYY-MM-DD HH:MM.'
            ]);
        }

        if ($endDt <= $startDt) {
            sendJson(400, [
                'status'  => 'error',
                'message' => 'Czas zakończenia musi być późniejszy niż czas rozpoczęcia.'
            ]);
        }

        $startSql = $startDt->format('Y-m-d H:i:s');
        $endSql   = $endDt->format('Y-m-d H:i:s');

        // Pobierz wszystkie zasoby wraz z flagą dostępności (StartA < EndB AND EndA > StartB)
        $stmtResources = $pdo->prepare(
            'SELECT
                r.id,
                r.name,
                r.type,
                r.capacity,
                CASE WHEN EXISTS (
                    SELECT 1
                    FROM reservations res
                    WHERE res.resource_id = r.id
                      AND res.start_time < :end_time
                      AND res.end_time > :start_time
                ) THEN 1 ELSE 0 END AS is_occupied
             FROM resources r
             ORDER BY r.id ASC'
        );
        $stmtResources->execute([
            ':start_time' => $startSql,
            ':end_time'   => $endSql,
        ]);

        $resources = array_map(static function (array $row): array {
            return [
                'id'          => (int)$row['id'],
                'name'        => $row['name'],
                'type'        => $row['type'],
                'capacity'    => (int)$row['capacity'],
                'is_occupied' => ((int)$row['is_occupied']) === 1,
            ];
        }, $stmtResources->fetchAll());

        // Pobierz listę aktywnych/nadchodzących rezerwacji
        $stmtReservations = $pdo->prepare(
            'SELECT
                res.id,
                res.resource_id,
                r.name AS resource_name,
                r.type AS resource_type,
                res.user_name,
                DATE_FORMAT(res.start_time, "%Y-%m-%d %H:%i") AS start_time,
                DATE_FORMAT(res.end_time, "%Y-%m-%d %H:%i") AS end_time,
                res.created_at
             FROM reservations res
             INNER JOIN resources r ON res.resource_id = r.id
             WHERE res.end_time >= NOW()
             ORDER BY res.start_time ASC'
        );
        $stmtReservations->execute();
        $reservations = $stmtReservations->fetchAll();

        sendJson(200, [
            'status'       => 'success',
            'resources'    => $resources,
            'reservations' => $reservations,
        ]);
    }

    if ($method === 'POST') {
        $rawBody = file_get_contents('php://input');
        $data = json_decode($rawBody ?: '', true);

        if (!is_array($data)) {
            sendJson(400, [
                'status'  => 'error',
                'message' => 'Nieprawidłowy format danych JSON w żądaniu.'
            ]);
        }

        $resourceId = filter_var($data['resource_id'] ?? null, FILTER_VALIDATE_INT);
        $rawUserName = trim((string)($data['user_name'] ?? ''));
        $startDt = parseDateTime((string)($data['start_time'] ?? ''));
        $endDt   = parseDateTime((string)($data['end_time'] ?? ''));

        if (!$resourceId || $resourceId <= 0) {
            sendJson(400, [
                'status'  => 'error',
                'message' => 'Wybierz poprawne stanowisko lub salę konferencyjną.'
            ]);
        }

        if (mb_strlen($rawUserName, 'UTF-8') < 3 || mb_strlen($rawUserName, 'UTF-8') > 100) {
            sendJson(400, [
                'status'  => 'error',
                'message' => 'Imię i nazwisko musi zawierać od 3 do 100 znaków.'
            ]);
        }

        if (!$startDt || !$endDt) {
            sendJson(400, [
                'status'  => 'error',
                'message' => 'Podaj poprawną datę oraz godziny rozpoczęcia i zakończenia.'
            ]);
        }

        // Margines 1 minuty dla opóźnień sieciowych przy rezerwacji na bieżącą minutę
        $nowThreshold = (new DateTimeImmutable('now'))->modify('-1 minute');
        if ($startDt < $nowThreshold) {
            sendJson(400, [
                'status'  => 'error',
                'message' => 'Nie można dokonać rezerwacji w przeszłości.'
            ]);
        }

        if ($endDt <= $startDt) {
            sendJson(400, [
                'status'  => 'error',
                'message' => 'Godzina zakończenia musi być późniejsza niż godzina rozpoczęcia.'
            ]);
        }

        // Oczyszczenie danych tekstowych przed zapisem
        $safeUserName = htmlspecialchars($rawUserName, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $startSql = $startDt->format('Y-m-d H:i:s');
        $endSql   = $endDt->format('Y-m-d H:i:s');

        $pdo->beginTransaction();

        // Sprawdź, czy zasób istnieje
        $stmtCheckResource = $pdo->prepare('SELECT id, name FROM resources WHERE id = :id FOR UPDATE');
        $stmtCheckResource->execute([':id' => $resourceId]);
        $resource = $stmtCheckResource->fetch();

        if (!$resource) {
            $pdo->rollBack();
            sendJson(404, [
                'status'  => 'error',
                'message' => 'Wybrane stanowisko lub sala nie istnieje w bazie danych.'
            ]);
        }

        // Wykrywanie kolizji czasowych: StartA < EndB AND EndA > StartB
        $stmtCollision = $pdo->prepare(
            'SELECT COUNT(*) AS conflicts
             FROM reservations
             WHERE resource_id = :resource_id
               AND start_time < :end_time
               AND end_time > :start_time'
        );
        $stmtCollision->execute([
            ':resource_id' => $resourceId,
            ':start_time'  => $startSql,
            ':end_time'    => $endSql,
        ]);

        $conflicts = (int)$stmtCollision->fetchColumn();

        if ($conflicts > 0) {
            $pdo->rollBack();
            sendJson(409, [
                'status'  => 'error',
                'message' => 'Konflikt terminów! Wybrane stanowisko jest już zajęte w tym przedziale czasowym.'
            ]);
        }

        // Zapis nowej rezerwacji
        $stmtInsert = $pdo->prepare(
            'INSERT INTO reservations (resource_id, user_name, start_time, end_time)
             VALUES (:resource_id, :user_name, :start_time, :end_time)'
        );
        $stmtInsert->execute([
            ':resource_id' => $resourceId,
            ':user_name'   => $safeUserName,
            ':start_time'  => $startSql,
            ':end_time'    => $endSql,
        ]);

        $newId = (int)$pdo->lastInsertId();
        $pdo->commit();

        sendJson(201, [
            'status'      => 'success',
            'message'     => 'Rezerwacja została pomyślnie utworzona!',
            'reservation' => [
                'id'            => $newId,
                'resource_id'   => $resourceId,
                'resource_name' => $resource['name'],
                'user_name'     => $safeUserName,
                'start_time'    => $startDt->format('Y-m-d H:i'),
                'end_time'      => $endDt->format('Y-m-d H:i'),
            ],
        ]);
    }

    if ($method === 'DELETE') {
        $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

        if (!$id) {
            $rawBody = file_get_contents('php://input');
            $data = json_decode($rawBody ?: '', true);
            if (is_array($data) && isset($data['id'])) {
                $id = filter_var($data['id'], FILTER_VALIDATE_INT);
            }
        }

        if (!$id || $id <= 0) {
            sendJson(400, [
                'status'  => 'error',
                'message' => 'Podaj poprawny identyfikator rezerwacji (id) do usunięcia.'
            ]);
        }

        $stmtDelete = $pdo->prepare('DELETE FROM reservations WHERE id = :id');
        $stmtDelete->execute([':id' => $id]);

        if ($stmtDelete->rowCount() === 0) {
            sendJson(404, [
                'status'  => 'error',
                'message' => 'Nie znaleziono rezerwacji o podanym identyfikatorze.'
            ]);
        }

        sendJson(200, [
            'status'  => 'success',
            'message' => 'Rezerwacja została pomyślnie anulowana.'
        ]);
    }

    sendJson(405, [
        'status'  => 'error',
        'message' => 'Niedozwolona metoda HTTP.'
    ]);
} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    sendJson(500, [
        'status'  => 'error',
        'message' => 'Wystąpił wewnętrzny błąd serwera podczas przetwarzania żądania.'
    ]);
}
