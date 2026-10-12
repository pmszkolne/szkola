<?php
$dane = ["Zadanie 1", "Zadanie 2", "Zadanie 3", "Test"];

header("Content-Type: application/json");
echo json_encode($dane);

?>
