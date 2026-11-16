<?php
declare(strict_types=1);

/**
 * Konfiguracja połączenia z bazą danych MySQL za pomocą PDO.
 */
$dbHost = '127.0.0.1';
$dbName = 'smart_office';
$dbUser = 'root';
$dbPass = '';
$dbCharset = 'utf8mb4';

$dsn = "mysql:host={$dbHost};dbname={$dbName};charset={$dbCharset}";

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $dbUser, $dbPass, $options);
} catch (PDOException $e) {
    http_response_code(500);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'status'  => 'error',
        'message' => 'Błąd połączenia z bazą danych. Sprawdź konfigurację serwera MySQL.'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}
