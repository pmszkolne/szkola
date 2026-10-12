<?php
$dane = ["ABC", "Przykladowe dane", "Test"];

header("Content-Type: application/json");
echo json_encode($dane);

?>
