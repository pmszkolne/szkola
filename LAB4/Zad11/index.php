<?php
$technologie = ["HMTL", "CSS", "JS", "PHP", "SQL"];

echo "<ul>";
foreach ($technologie as $v) {
    echo "<li>" . htmlspecialchars($v) . "</li>";
}
echo "</ul>";
?>