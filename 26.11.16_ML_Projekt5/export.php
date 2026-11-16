<?php
declare(strict_types=1);

require_once __DIR__ . '/db.php';

function sanitizeCsvCell(string $value): string
{
    if ($value !== '' && in_array($value[0], ['=', '+', '-', '@', "\t", "\r"], true)) {
        return "'" . $value;
    }
    return $value;
}

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

$stmt = $pdo->query(
    'SELECT
        t.id,
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
$tickets = $stmt->fetchAll();

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="zgloszenia_it.csv"');
header('X-Content-Type-Options: nosniff');
header('Pragma: no-cache');
header('Expires: 0');

$output = fopen('php://output', 'w');

fwrite($output, "\xEF\xBB\xBF");

fputcsv($output, [
    'ID',
    'Kategoria',
    'Sala',
    'Tytuł zgłoszenia',
    'Opis problemu',
    'Priorytet',
    'Status',
    'Data utworzenia',
], ';');

foreach ($tickets as $ticket) {
    $priorityText = $priorityLabels[$ticket['priority']] ?? $ticket['priority'];
    $statusText   = $statusLabels[$ticket['status']] ?? $ticket['status'];

    fputcsv($output, [
        $ticket['id'],
        sanitizeCsvCell((string)$ticket['category_name']),
        sanitizeCsvCell((string)$ticket['room']),
        sanitizeCsvCell((string)$ticket['title']),
        sanitizeCsvCell((string)$ticket['description']),
        sanitizeCsvCell($priorityText),
        sanitizeCsvCell($statusText),
        $ticket['created_at'],
    ], ';');
}

fclose($output);
exit;