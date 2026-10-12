<?php

$zadanie = $_POST["zadanie"] ?? "";
$zadanie = trim($zadanie);

if ($zadanie !== "") {
    echo htmlspecialchars($zadanie);
}
?>


<form method="POST">
    <input name="zadanie">
    <button>Dodaj</button>
</form>