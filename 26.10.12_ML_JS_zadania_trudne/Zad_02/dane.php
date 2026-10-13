<?php
$q = $_GET["q"] ?? ""; // query
$q = strtolower($q); // lowercase

$data = ["ABC", "Przykladowe dane", "Test"];
$results = [];

foreach ($data as $item) {
    $item = strtolower($item); // lowercase
    if (str_contains($item, $q)) { $results[] = $item; }
}

// send response
header("Content-Type: application/json; charset=utf-8");
echo json_encode($results);

?>
