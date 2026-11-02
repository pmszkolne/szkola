<?php
$blad = false;
$postac = null;

$name = trim($_POST['imie'] ?? '');
$klasa = $_POST['klasa'] ?? '';
$sila = filter_input(INPUT_POST, 'sila', FILTER_VALIDATE_INT);
$zrecznosc = filter_input(INPUT_POST, 'zrecznosc', FILTER_VALIDATE_INT);
$inteligencja = filter_input(INPUT_POST, 'inteligencja', FILTER_VALIDATE_INT);

// Walidacja 

if (empty($name)) { $blad = true; }
if (!in_array($klasa, ['Wojownik', 'Mag', 'Łotrzyk'])) { $blad = true; }
if ($sila === false || $sila < 1 || $sila > 20) { $blad = true; }
if ($zrecznosc === false || $zrecznosc < 1 || $zrecznosc > 20) { $blad = true; }
if ($inteligencja === false || $inteligencja < 1 || $inteligencja > 20) { $blad = true; }

if (!$blad) {
    $hp = ($sila * 10) + ($zrecznosc * 2);
    
    $postac = [
        'imie' => htmlspecialchars($name),
        'klasa' => $klasa,
        'sila' => $sila,
        'zrecznosc' => $zrecznosc,
        'inteligencja' => $inteligencja,
        'hp' => $hp
    ];
}
?>

<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='utf-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1'>
    <title>Projekt kart RPG</title>
    <link rel='stylesheet' href='/style.css'>
    <script src="/app.js" defer></script>
</head>
<body>
    <form method="post">
        <!-- IMIE -->
        <label for="imie">Imię postaci</label>
        <input 
            type="text" 
            id="imie" 
            name="imie" 
            required
            value="<?= htmlspecialchars($_POST['imie'] ?? '') ?>"
        >

        <!-- KLASA -->
         <label for="klasa">Klasa</label>
         <select
            id="klasa"
            name="klasa"
            required
         >
            <option value="" disabled hidden <?= empty($_POST['klasa']) ? 'selected' : '' ?>></option>
            <option value="Wojownik" <?= ($_POST['klasa'] ?? '') === 'Wojownik' ? 'selected' : '' ?>>Wojownik</option>
            <option value="Mag" <?= ($_POST['klasa'] ?? '') === 'Mag' ? 'selected' : '' ?>>Mag</option>
            <option value="Łotrzyk" <?= ($_POST['klasa'] ?? '') === 'Łotrzyk' ? 'selected' : '' ?>>Łotrzyk</option>
        </select>

        <!-- SILA -->
        <label for="sila">Siła (1-20)</label>
        <input 
            id="sila"
            name="sila"
            type="number" 
            min="1" 
            max="100" 
            step="1"
            required
            value="<?= htmlspecialchars($_POST['sila'] ?? '') ?>"
        >

        <!-- ZRECZNOSC -->
        <label for="zrecznosc">Zręczność (1-20)</label>
        <input 
            id="zrecznosc"
            name="zrecznosc"
            type="number" 
            min="1" 
            max="20" 
            step="1"
            required
            value="<?= htmlspecialchars($_POST['zrecznosc'] ?? '') ?>"
        >

        <!-- INTELIGENCJA -->
        <label for="inteligencja">Inteligencja (1-20)</label>
        <input 
            id="inteligencja"
            name="inteligencja"
            type="number" 
            min="1" 
            max="20" 
            step="1"
            required
            value="<?= htmlspecialchars($_POST['inteligencja'] ?? '') ?>"
        >

        <!-- WYSLIJ -->
        <button 
            id="submit" 
            type="submit">
            Wyślij
        </button> 
    </form>

    <?php if ($postac): ?>
    <div id="karta" class="<?= $postac['klasa'] ?>">
        <div class="header">
            <h2 id="imie"><?= $postac['imie'] ?></h2>
            <div id="class"><?= $postac['klasa'] ?></div>
        </div>
        <hr>
        <div class="stats">
            <div class="box">
                <div class="title">HP</div>
                <div class="value"><?= $postac['hp'] ?></div>
            </div>
            <div class="box">
                <div class="title">Siła</div>
                <div class="value"><?= $postac['sila'] ?></div>
            </div>
            <div class="box">
                <div class="title">Zręczność</div>
                <div class="value"><?= $postac['zrecznosc'] ?></div>
            </div>
            <div class="box">
                <div class="title">Inteligencja</div>
                <div class="value"><?= $postac['inteligencja'] ?></div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</body>
</html>