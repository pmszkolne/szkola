<?php

?>

<!DOCTYPE html>

<html>
<head>
</head>
    <title>Test</title>
<body>
    <ul id="lista">
    </ul>

    <script defer>
const lista = document.querySelector("#lista");

async function pobierzDane(params) {
    try {
        // fetch
        const response = await fetch("json.php")
        if (!response.ok) { throw new Error ("Brak odpowiedzi z json.php!"); }

        
        const dane = await response.json();

        // render
        for (const v of dane) {
            console.log(v);
        }
    } catch (e) {
        console.log(e);
    }
}

pobierzDane();
console.log("test");
    </script>
</body>
</html>

