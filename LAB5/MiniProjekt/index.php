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
    <form method="post" novalidate>
        <!-- IMIE -->
        <input 
            type="text" 
            id="imie" 
            name="imie" 
            placeholder="Imie" 
            required>
        <p id="bladImie" class="blad"></p>

        <!-- EMAIL -->
        <input 
            type="email" 
            id="email" 
            name="email" 
            placeholder="email@example.com"
            pattern="[a-zA-Z0-9._%+\x2D]+@[a-zA-Z0-9.\x2D]+\.[a-zA-Z]{2,}"
            required>
        <p id="bladEmail" class="blad"></p>

        <!-- WIADOMOSC -->
        <textarea 
            id="wiadomosc" 
            name="wiadomosc" 
            rows="3" 
            maxlength="200" 
            placeholder="Wiadomość" 
            required></textarea>
        <p id="bladWiadomosc" class="blad"></p>
        <p id="znaki">Wpisanych znaków: 0, pozostało: 200</p>

        <!-- WYSLIJ -->
        <button 
            id="submit" 
            type="submit">
            Wyślij
        </button>
        <p id="bladWysylanie" class="blad"></p>
    </form>
</body>
</html>