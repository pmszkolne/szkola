<?php
$imie = $_POST["imie"] ?? "";
$temat = $_POST["temat"] ?? "";
$email = $_POST["email"] ?? "";
$wiadomosc = $_POST["wiadomosc"] ?? "";
$zgoda = $_POST["zgoda"] ?? "";

$imie = trim($imie);
$wiadomosc = trim($wiadomosc);

$poprawne = true;

// --- Display info ---

echo "Temat: " . htmlspecialchars($temat, ENT_QUOTES, "UTF-8") . "<br>";
echo "Imie: " . htmlspecialchars($imie, ENT_QUOTES, "UTF-8") . "<br>";
echo "<br>";

// --- CHECKS ---

// email -> not empty, valid
if (isset($_POST["email"])) {
    // validity check
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $poprawne = false;
        echo "Nieporawny email!<br>";
    }
} else {
    $poprawne = false;
    echo "Podaj adres email!<br>";
}

// imie -> not empty
if ($imie === "") {
    $poprawne = false;
    echo "Imię jest puste!<br>";
}

// wiadomosc -> not empty
if ($wiadomosc === "") {
    $poprawne = false;
    echo "Wiadomość jest pusta!<br>";
}

// zgoda -> checked
if ($zgoda !== "on") {
    $poprawne = false;
    echo "Musisz zaakceptować regulamin!<br>";
}

// SUCCESS
if ($poprawne) {
    echo "Dziękujemy!";
}
?>
