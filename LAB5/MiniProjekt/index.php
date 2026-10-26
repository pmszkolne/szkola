<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='utf-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1'>
    <title>Formularz kontaktowy</title>
    <link rel='stylesheet' href='/MiniProjekt/style.css'>
    <script src="/MiniProjekt/app.js" defer></script>
</head>
<body>
    <form>
        <input type="text" id="imie" name="imie" placeholder="Imię..." required>
        <input type="email" id="email" name="email" placeholder="email@example.com" required>
        <textarea id="wiadomosc" name="wiadomosc" rows="3" placeholder="Twoja wiadomość..." maxlength="200" required></textarea>
        <p id="znaki">Wpisanych znaków: 0, pozostało: 200</p>

        <button type="submit" id="submit">Wyślij</button>
    </form>
</body>
</html>