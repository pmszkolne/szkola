<?php
header("Content-Type: application/json; charset=UTF-8");

$imie = $_POST["imie"] ?? "";
$email = $_POST["email"] ?? "";
$wiadomosc = $_POST["wiadomosc"] ?? "";

if ($imie === "" || $email === "")

$poprawne = true;

$imie = trim($imie);
$wiadomosc = trim($wiadomosc);

//
echo "Temat: " . htmlspecialchars($temat, ENT_QUOTES, "UTF-8") . "<br>";
echo "Imie: " . htmlspecialchars($imie, ENT_QUOTES, "UTF-8") . "<br>";


// email sent
if (isset($_POST["email"])) {
    echo "Otrzymano adres e-mail.<br>";

    // email check
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $poprawne = false;
        echo "Podaj poprawny adres e-mail.<br>";
    }
} else {
    $poprawne = false;
    echo "Nie przesłano adresu e-mail.<br>";
}

// empty check
if ($imie === "") {
    $poprawne = false;
    echo "Imię jest puste!.<br>";
}

if ($wiadomosc === "") {
    $poprawne = false;
    echo "Wiadomość jest pusta!.<br>";
}

if ($zgoda !== "on") {
    $poprawne = false;
    echo "Musisz zaakceptować regulamin.<br>";
}

if ($poprawne) {
    echo "<br>Dziękujemy!";
}


?>
