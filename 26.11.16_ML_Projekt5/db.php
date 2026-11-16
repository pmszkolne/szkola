<?php
declare(strict_types=1);

$dbHost = 'localhost';
$dbName = 'helpdesk_db';$dbUser = 'root';
$dbPass = '';$dbCharset = 'utf8mb4';

$dsn = "mysql:host={$dbHost};dbname={$dbName};charset={$dbCharset}";

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn,$dbUser, $dbPass,$options);
} catch (PDOException $e) {
    error_log('Błąd połączenia z bazą danych MySQL: ' . $e->getMessage());
    http_response_code(500);
    die('Wystąpił wewnętrzny błąd serwera bazy danych. Skontaktuj się z administratorem.');
}