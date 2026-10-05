<?php
$produkty = [
    ["id" => 1, "nazwa" => "Jablko", "cena" => 5, "dostepny" => true],
    ["id" => 2, "nazwa" => "Banan", "cena" => 3, "dostepny" => true],
    ["id" => 3, "nazwa" => "iPhone", "cena" => 1000, "dostepny" => false]
];

$dostepne = array_filter($produkty, function($v) { return $v["dostepny"]; });

echo "<ol>";
foreach ($dostepne as $v) {
    $teskt = $v["nazwa"];
    $teskt = $teskt . ", " . number_format($v["cena"], 2) . " zł";
    $teskt = $teskt . ", " . ($v["dostepny"] ? "dostepny" : "niedostepny");

    echo "<li>" . htmlspecialchars($teskt) . "</li>";
}
echo "</ol>";

echo "Liczba produktów: " . count($dostepne)
?>