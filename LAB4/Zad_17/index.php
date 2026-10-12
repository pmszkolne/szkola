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

function render(items) {
    for (item of items) {
        const newItem = document.createElement("li");
        newItem.textContent = item;

        lista.append(newItem);
    }
}

async function pobierzDane(params) {
    try {
        // fetch
        const response = await fetch(window.location.pathname + "/json.php");
        if (!response.ok) { throw new Error ("Brak odpowiedzi z json.php!"); }
        
        const dane = await response.json();

        // render
        render(dane)
        
    } catch (e) {
        console.log(e);
    }
}

pobierzDane();
console.log("test");
    </script>
</body>
</html>

