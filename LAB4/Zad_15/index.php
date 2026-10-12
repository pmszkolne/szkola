<?php
session_start();

if (! isset($_SESSION["zadania"])) {
    $_SESSION["zadania"] = [];
}

$zadanie = $_POST["zadanie"] ?? "";
$zadanie = trim($zadanie);

if ($zadanie !== "") {
    //echo htmlspecialchars($zadanie);
    $_SESSION["zadania"][] = $zadanie;
}

?>

<!DOCTYPE html>

<html>
<head>
</head>
    <title>Test</title>
<body>
    <form method="POST">
        <input name="zadanie">
        <button>Dodaj</button>
    </form>

    <ul>
        <?php foreach ($_SESSION["zadania"] as $v): ?>
        <li><?= htmlspecialchars($v) ?></li>
        <?php endforeach; ?>
    </ul>
</body>
</html>

