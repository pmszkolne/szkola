<?php
header("Content-Type: application/json; charset=utf-8");

$dane = ["ABC", "Przykladowe dane", "Test"];

header("Content-Type: application/json");
echo json_encode($dane);

?>
