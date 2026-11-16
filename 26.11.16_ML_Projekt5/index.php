<?php
declare(strict_types=1);

session_set_cookie_params([
    'httponly' => true,
    'samesite' => 'Lax',
]);

session_start();

header("Content-Security-Policy: default-src 'self'; script-src 'self'; style-src 'self'; img-src 'self' data:; form-action 'self'; frame-ancestors 'none';");
header('X-Frame-Options: DENY');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: strict-origin-when-cross-origin');

require_once __DIR__ . '/db.php';

/**
 * Funkcja pomocnicza chroniąca przed atakami XSS
 */
function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrfToken = $_SESSION['csrf_token'];

/**
 * Mapowanie kodów priorytetów i statusów na czytelne etykiety
 */


$priorityLabels = [
    'niski'  => 'Niski',
    'sredni' => 'Średni',
    'wysoki' => 'Wysoki',
];

$statusLabels = [
    'nowe'      => 'Nowe',
    'w_trakcie' => 'W trakcie',
    'zamkniete' => 'Zamknięte',
];

// Obsługa żądań POST (CRUD + wzorzec Post/Redirect/Get)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $postedToken = (string)($_POST['csrf_token'] ?? '');

    if ($postedToken === '' || !hash_equals($csrfToken, $postedToken)) {
        $_SESSION['flash'] = [
            'type' => 'error',
            'text' => 'Błąd weryfikacji bezpieczeństwa (nieprawidłowy token CSRF). Odśwież stronę i spróbuj ponownie.',
        ];
        header('Location: index.php');
        exit;
    }

    $action = $_POST['action'] ?? '';

    if ($action === 'add_ticket') {
        $categoryId  = filter_input(INPUT_POST, 'category_id', FILTER_VALIDATE_INT);
        $room        = trim((string)($_POST['room'] ?? ''));
        $title       = trim((string)($_POST['title'] ?? ''));
        $description = trim((string)($_POST['description'] ?? ''));
        $priority    = (string)($_POST['priority'] ?? 'sredni');

        $errors = [];

        if (!$categoryId || $categoryId <= 0) {
            $errors[] = 'Wybierz poprawną kategorię usterki.';
        }
        if ($room === '' || mb_strlen($room, 'UTF-8') > 20) {
            $errors[] = 'Numer sali jest wymagany (maks. 20 znaków).';
        }
        if (mb_strlen($title, 'UTF-8') < 5 || mb_strlen($title, 'UTF-8') > 100) {
            $errors[] = 'Tytuł zgłoszenia musi mieć od 5 do 100 znaków.';
        }
        if ($description === '' || mb_strlen($description, 'UTF-8') > 300) {
            $errors[] = 'Opis usterki jest wymagany i może zawierać maksymalnie 300 znaków.';
        }
        if (!array_key_exists($priority, $priorityLabels)) {
            $errors[] = 'Wybrano nieprawidłowy priorytet.';
        }

        if (empty($errors)) {
            // Weryfikacja istnienia kategorii w bazie
            $catCheck = $pdo->prepare('SELECT id FROM categories WHERE id = :id');
            $catCheck->execute([':id' => $categoryId]);

            if ($catCheck->fetch()) {
                $stmt = $pdo->prepare(
                    'INSERT INTO tickets (category_id, room, title, description, priority, status)
                     VALUES (:category_id, :room, :title, :description, :priority, :status)'
                );
                $stmt->execute([
                    ':category_id' => $categoryId,
                    ':room'        => $room,
                    ':title'       => $title,
                    ':description' => $description,
                    ':priority'    => $priority,
                    ':status'      => 'nowe',
                ]);

                $_SESSION['flash'] = [
                    'type' => 'success',
                    'text' => 'Nowe zgłoszenie zostało pomyślnie zarejestrowane w systemie.',
                ];
            } else {
                $_SESSION['flash'] = [
                    'type' => 'error',
                    'text' => 'Wybrana kategoria nie istnieje w bazie danych.',
                ];
            }
        } else {
            $_SESSION['flash'] = [
                'type' => 'error',
                'text' => implode(' ', $errors),
            ];
            $_SESSION['old'] = [
                'category_id' => $categoryId,
                'room'        => $room,
                'title'       => $title,
                'description' => $description,
                'priority'    => $priority,
            ];
        }

        // Wzorzec PRG (Post/Redirect/Get)
        header('Location: index.php');
        exit;
    }

    if ($action === 'update_status') {
        $ticketId  = filter_input(INPUT_POST, 'ticket_id', FILTER_VALIDATE_INT);
        $newStatus = (string)($_POST['new_status'] ?? '');

        if ($ticketId && $ticketId > 0 && array_key_exists($newStatus, $statusLabels)) {
            $stmt = $pdo->prepare('UPDATE tickets SET status = :status WHERE id = :id');
            $stmt->execute([
                ':status' => $newStatus,
                ':id'     => $ticketId,
            ]);

            $_SESSION['flash'] = [
                'type' => 'success',
                'text' => 'Status zgłoszenia #' . $ticketId . ' został zaktualizowany.',
            ];
        }

        header('Location: index.php');
        exit;
    }

    if ($action === 'delete_ticket') {
        $ticketId = filter_input(INPUT_POST, 'ticket_id', FILTER_VALIDATE_INT);

        if ($ticketId && $ticketId > 0) {
            // Usuwanie dozwolone wyłącznie dla zgłoszeń o statusie 'zamkniete'
            $stmt = $pdo->prepare("DELETE FROM tickets WHERE id = :id AND status = 'zamkniete'");
            $stmt->execute([':id' => $ticketId]);

            if ($stmt->rowCount() > 0) {
                $_SESSION['flash'] = [
                    'type' => 'success',
                    'text' => 'Zamknięte zgłoszenie #' . $ticketId . ' zostało usunięte.',
                ];
            } else {
                $_SESSION['flash'] = [
                    'type' => 'error',
                    'text' => 'Można usunąć wyłącznie zgłoszenie oznaczone jako zamknięte.',
                ];
            }
        }

        header('Location: index.php');
        exit;
    }
}

// Pobranie komunikatów sesyjnych i poprzednich danych formularza
$flash = $_SESSION['flash'] ?? null;
$old   = $_SESSION['old'] ?? [];
unset($_SESSION['flash'], $_SESSION['old']);

// Pobranie listy kategorii
$categoriesStmt = $pdo->query('SELECT id, name FROM categories ORDER BY id ASC');
$categories = $categoriesStmt->fetchAll();

// Pobranie listy zgłoszeń wraz z nazwą kategorii (SELECT JOIN, od najnowszych)
$ticketsStmt = $pdo->query(
    'SELECT
        t.id,
        t.category_id,
        c.name AS category_name,
        t.room,
        t.title,
        t.description,
        t.priority,
        t.status,
        t.created_at
     FROM tickets t
     INNER JOIN categories c ON t.category_id = c.id
     ORDER BY t.created_at DESC, t.id DESC'
);
$tickets = $ticketsStmt->fetchAll();

// Obliczenie podsumowania liczbowego w podziale na statusy
$counts = [
    'nowe'      => 0,
    'w_trakcie' => 0,
    'zamkniete' => 0,
];

foreach ($tickets as $ticket) {
    if (isset($counts[$ticket['status']])) {
        $counts[$ticket['status']]++;
    }
}
$totalCount = count($tickets);
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System Zgłoszeń Serwisowych IT — Helpdesk</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <header class="app-header">
        <div class="container header-inner">
            <div>
                <h1>Helpdesk IT — System Zgłoszeń Serwisowych</h1>
                <p class="subtitle">Szkolny punkt wsparcia technicznego sprzętu i oprogramowania</p>
            </div>
            <div class="header-actions">
                <a href="export.php" class="btn btn-export" title="Pobierz zestawienie wszystkich zgłoszeń w pliku CSV">
                    Eksportuj do CSV
                </a>
            </div>
        </div>
    </header>

    <main class="container">
        <?php if ($flash !== null): ?>
            <div class="alert alert-<?= e($flash['type']) ?>" role="status">
                <?= e($flash['text']) ?>
            </div>
        <?php endif; ?>

        <!-- Pasek statystyk zgłoszeń -->
        <section class="stats-bar" aria-label="Podsumowanie zgłoszeń">
            <div class="stat-card stat-nowe">
                <span class="stat-label">Nowe</span>
                <strong class="stat-value"><?= $counts['nowe'] ?></strong>
            </div>
            <div class="stat-card stat-w-trakcie">
                <span class="stat-label">W trakcie realizacji</span>
                <strong class="stat-value"><?= $counts['w_trakcie'] ?></strong>
            </div>
            <div class="stat-card stat-zamkniete">
                <span class="stat-label">Zamknięte</span>
                <strong class="stat-value"><?= $counts['zamkniete'] ?></strong>
            </div>
            <div class="stat-card stat-all">
                <span class="stat-label">Wszystkie zgłoszenia</span>
                <strong class="stat-value"><?= $totalCount ?></strong>
            </div>
        </section>

        <!-- Główny układ dwukolumnowy (35% formularz / 65% lista zgłoszeń) -->
        <div class="layout-grid">
            <!-- Panel boczny z formularzem dodawania zgłoszenia -->
            <aside class="sidebar card">
                <h2>Nowe zgłoszenie usterki</h2>
                <form id="ticket-form" action="index.php" method="POST" novalidate>
                    <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">

                    <input type="hidden" name="action" value="add_ticket">

                    <div id="form-errors" class="form-errors" role="alert" hidden></div>

                    <div class="form-group">
                        <label for="category_id">Kategoria usterki *</label>
                        <select id="category_id" name="category_id" required>
                            <?php foreach ($categories as $category): ?>
                                <?php $selected = ((int)($old['category_id'] ?? 0) === (int)$category['id']) ? 'selected' : ''; ?>
                                <option value="<?= (int)$category['id'] ?>" <?= $selected ?>>
                                    <?= e($category['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="room">Numer sali / lokalizacja *</label>
                        <input
                            type="text"
                            id="room"
                            name="room"
                            maxlength="20"
                            placeholder="np. Sala 204 lub Sekretariat"
                            value="<?= e((string)($old['room'] ?? '')) ?>"
                            required
                        >
                    </div>

                    <div class="form-group">
                        <label for="title">Tytuł zgłoszenia * <span class="hint">(min. 5 znaków)</span></label>
                        <input
                            type="text"
                            id="title"
                            name="title"
                            minlength="5"
                            maxlength="100"
                            placeholder="np. Brak obrazu na monitorze"
                            value="<?= e((string)($old['title'] ?? '')) ?>"
                            required
                        >
                    </div>

                    <div class="form-group">
                        <label for="priority">Priorytet *</label>
                        <select id="priority" name="priority" required>
                            <?php $oldPriority = (string)($old['priority'] ?? 'sredni'); ?>
                            <option value="niski" <?= $oldPriority === 'niski' ? 'selected' : '' ?>>Niski</option>
                            <option value="sredni" <?= $oldPriority === 'sredni' ? 'selected' : '' ?>>Średni</option>
                            <option value="wysoki" <?= $oldPriority === 'wysoki' ? 'selected' : '' ?>>Wysoki</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="description">Opis problemu *</label>
                        <textarea
                            id="description"
                            name="description"
                            rows="5"
                            maxlength="300"
                            placeholder="Opisz dokładnie objawy usterki, numer stanowiska itp."
                            required
                        ><?= e((string)($old['description'] ?? '')) ?></textarea>
                        <div class="char-counter" id="char-counter">
                            <span id="char-count">0</span> / 300 znaków
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary btn-block">Wyślij zgłoszenie</button>
                </form>
            </aside>

            <!-- Główna sekcja z wyszukiwarką i listą ticketów -->
            <section class="tickets-section" aria-label="Lista zgłoszeń serwisowych">
                <div class="toolbar card">
                    <div class="search-box">
                        <label for="search-input" class="visually-hidden">Szukaj zgłoszeń</label>
                        <input
                            type="search"
                            id="search-input"
                            placeholder="Szukaj po tytule, opisie, sali lub kategorii..."
                            autocomplete="off"
                        >
                    </div>
                    <div class="filter-buttons" role="group" aria-label="Filtruj według statusu">
                        <button type="button" class="filter-btn active" data-filter="all">Wszystkie</button>
                        <button type="button" class="filter-btn" data-filter="nowe">Nowe</button>
                        <button type="button" class="filter-btn" data-filter="w_trakcie">W trakcie</button>
                        <button type="button" class="filter-btn" data-filter="zamkniete">Zamknięte</button>
                    </div>
                </div>

                <?php if (empty($tickets)): ?>
                    <div class="card empty-state">
                        <p>Brak zgłoszeń w bazie danych. Użyj formularza obok, aby dodać pierwsze zgłoszenie.</p>
                    </div>
                <?php else: ?>
                    <div id="tickets-list" class="tickets-list">
                        <?php foreach ($tickets as $ticket): ?>
                            <?php
                                $id           = (int)$ticket['id'];
                                $priority     = $ticket['priority'];
                                $status       = $ticket['status'];
                                $priorityText = $priorityLabels[$priority] ?? $priority;
                                $statusText   = $statusLabels[$status] ?? $status;
                                $formattedDate = date('d.m.Y H:i', strtotime($ticket['created_at']));
                            ?>
                            <article
                                class="ticket-card priority-<?= e($priority) ?> status-card-<?= e($status) ?>"
                                data-status="<?= e($status) ?>"
                            >
                                <div class="ticket-header">
                                    <div class="ticket-meta">
                                        <span class="ticket-id">#<?= $id ?></span>
                                        <span class="badge badge-category"><?= e($ticket['category_name']) ?></span>
                                        <span class="badge badge-room"><?= e($ticket['room']) ?></span>
                                    </div>
                                    <div class="ticket-badges">
                                        <span class="badge badge-priority-<?= e($priority) ?>">
                                            Priorytet: <?= e($priorityText) ?>
                                        </span>
                                        <span class="badge badge-status-<?= e($status) ?>">
                                            <?= e($statusText) ?>
                                        </span>
                                    </div>
                                </div>

                                <h3 class="ticket-title"><?= e($ticket['title']) ?></h3>
                                <p class="ticket-description"><?= nl2br(e($ticket['description'])) ?></p>

                                <div class="ticket-footer">
                                    <time class="ticket-date" datetime="<?= e($ticket['created_at']) ?>">
                                        Zgłoszono: <?= e($formattedDate) ?>
                                    </time>

                                    <div class="ticket-actions">
                                        <?php if ($status === 'nowe'): ?>
                                            <form action="index.php" method="POST" class="inline-form">
                                                <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">
                                                <input type="hidden" name="action" value="update_status">
                                                <input type="hidden" name="ticket_id" value="<?= $id ?>">
                                                <input type="hidden" name="new_status" value="w_trakcie">
                                                <button type="submit" class="btn btn-sm btn-warning">
                                                    Przyjmij do realizacji
                                                </button>
                                            </form>
                                        <?php elseif ($status === 'w_trakcie'): ?>
                                            <form action="index.php" method="POST" class="inline-form">
                                                <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">
                                                <input type="hidden" name="action" value="update_status">
                                                <input type="hidden" name="ticket_id" value="<?= $id ?>">
                                                <input type="hidden" name="new_status" value="zamkniete">
                                                <button type="submit" class="btn btn-sm btn-success">
                                                    Oznacz jako zamknięte
                                                </button>
                                            </form>
                                        <?php elseif ($status === 'zamkniete'): ?>
                                            <form action="index.php" method="POST" class="inline-form">
                                                <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">
                                                <input type="hidden" name="action" value="update_status">
                                                <input type="hidden" name="ticket_id" value="<?= $id ?>">
                                                <input type="hidden" name="new_status" value="w_trakcie">
                                                <button type="submit" class="btn btn-sm btn-secondary">
                                                    Wznów zgłoszenie
                                                </button>
                                            </form>

                                            <form action="index.php" method="POST" class="inline-form delete-form">
                                                <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">
                                                <input type="hidden" name="action" value="delete_ticket">
                                                <input type="hidden" name="ticket_id" value="<?= $id ?>">
                                                <button type="submit" class="btn btn-sm btn-danger">
                                                    Usuń
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>

                    <div id="no-results" class="card empty-state" hidden>
                        <p>Brak zgłoszeń spełniających wybrane kryteria wyszukiwania.</p>
                    </div>
                <?php endif; ?>
            </section>
        </div>
    </main>

    <footer class="app-footer">
        <div class="container">
            <p>System Zgłoszeń Helpdesk IT</p>
        </div>
    </footer>

    <script src="js/script.js"></script>
</body>
</html>
