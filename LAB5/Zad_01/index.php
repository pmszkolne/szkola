<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='utf-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1'>
    <title>Formularz kontaktowy</title>
    <link rel='stylesheet' href='style.css'>
    <script src="app.js" defer></script>
</head>
<body>
    <form method="post" action="odbierz.php" novalidate>
        <!-- IMIE -->
        <label>Imię</label>
        <input type="text" id="imie" name="imie" required>
        <p id="bladImie" class="blad"></p>

        <!-- EMAIL -->
        <input type="email" id="email" name="email" placeholder="email@example.com" required>
        <p id="bladEmail" class="blad"></p>

        <!-- WIADOMOSC -->
        <textarea id="wiadomosc" name="wiadomosc" rows="3" maxlength="200" required></textarea>
        <p id="bladWiadomosc" class="blad"></p>
        <p id="znaki">Wpisanych znaków: 0</p>

        <!-- TEMAT -->
        <select id="temat" name="temat">
            <option>Pytanie</option>
            <option>Problem techniczny</option>
            <option>Inne</option>
        </select>
        <br>
        
        <!-- CHECKBOX -->
        <input type="checkbox" id="zgoda" name="zgoda" required> Akceptuje regulamin
        <p id="bladZgoda" class="blad"></p>

        <!-- WYSLIJ -->
        <button id="submit" type="submit">Wyślij</button>
    </form>
    
    <p id="witaj"></p>
</body>
</html>