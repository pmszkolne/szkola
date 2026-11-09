<?php
// godzina
$godzina = date('H');

// powitanie
if ($godzina >= 6 && $godzina < 18) 
    {$powitanie = "Dzień dobry!";
} else {
    $powitanie = "Dobry wieczór!";
}

// cytat
$cytaty = [
    "Logika zaprowadzi cię z punktu A do punktu B. Wyobraźnia zaprowadzi cię wszędzie. - Albert Einstein",
    "Pudłujesz 100% strzałów, jeśli w ogóle ich nie wykonujesz. - Wayne Gretzky",
    "Najtrudniejsze jest zdecydowanie się na działanie, reszta to już tylko kwestia wytrwałości. - Amelia Earhart",
    "Sukces to nie to, co masz, ale to, kim jesteś. - Bo Bennett"
];

$cytat = $cytaty[ array_rand($cytaty) ];
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Karta powitalna</title>
    <link rel="stylesheet" href="style.css">
    <script src="script.js" defer></script>
</head>
<body>
    <div class="karta">
        <h1><?php echo $powitanie; ?></h1>
        <p class="cytat">„<?php echo $cytat; ?>”</p>
        <button id="zmienMotyw">Zmień motyw</button>
        <button id="innyCytat">Losuj inny cytat</button>
    </div>
</body>
</html>