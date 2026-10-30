<?php
header("Content-Type: application/json; charset=UTF-8");

// Odbieranie danych
$json = file_get_contents("php://input");
$dane = json_decode($json, true);

$imie = trim($dane["imie"]) ?? "";
$email = trim($dane["email"]) ?? "";
$wiadomosc = trim($dane["wiadomosc"]) ?? "";

// Walidacja
$sukces = 
    $imie !== "" &&
    $email !== "" &&
    $wiadomosc !== "" &&
    filter_var($email, FILTER_VALIDATE_EMAIL) !== false;

// echo json_encode([ // testing
//     $imie, $email, $wiadomosc, $suckes
// ]);

echo json_encode([
    "sukces" => $sukces
]);
?>
