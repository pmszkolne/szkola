const submitObj = document.querySelector("#submit");

const imieObj = document.querySelector("#imie");
const wiadomoscObj = document.querySelector("#wiadomosc");
const emailObj = document.querySelector("#email");

const znakiObj = document.querySelector("#znaki");
const bladImieObj = document.querySelector("#bladImie");
const bladWiadomoscObj = document.querySelector("#bladWiadomosc");
const bladEmailObj = document.querySelector("#bladEmail");
const bladWysylanieObj = document.querySelector("#bladWysylanie");

const formularz = document.querySelector("form");

// Liczenie znakow
function liczZnaki() {
    const znaki = wiadomoscObj.value.length
    const maxLength = wiadomoscObj.maxLength

    znakiObj.textContent = "Wpisanych znaków: " + znaki + ", pozostało: " + (maxLength - znaki);
}
liczZnaki();

wiadomoscObj.addEventListener("input", liczZnaki);

// Wysylanie
async function wyslij(imie, email, wiadomosc) {    
    try {
        // Wysylanie
        const odpowiedz = await fetch("/MiniProjekt/odpowiedz.php", {
            method: "POST",
            headers: {"Content-Type": "application/json"},
            body: JSON.stringify({
                imie: imie,
                email: email,
                wiadomosc: wiadomosc
            })
        });
        if (!odpowiedz.ok) { throw new Error("Błąd HTTP: " + odpowiedz.status); }

        // Odbieranie
        const dane = await odpowiedz.json()

        if (dane.sukces) {
            formularz.reset();
            bladWysylanieObj.textContent = "";
            liczZnaki();
            
            alert("Wysłano!");
            console.log("Wysłano formularz poprawnie.");
        } else {
            throw new Error("Nieporawne dane!");
        }
    } catch (e) {
        console.error("Bląd przy wysyłaniu: ", e);
        bladWysylanieObj.textContent = "Wystąpił błąd przy wysyłaniu!"
    }
}

// Walidacja
formularz.addEventListener("submit", (e) => {
    event.preventDefault(); // WAZNE, inaczej nie dziala fetch()

    const imie = imieObj.value.trim();
    const email = emailObj.value.trim();
    const wiadomosc = wiadomoscObj.value.trim();

    let blad = false;

    // IMIE
    if (imie === "") {
        bladImieObj.textContent = "Wymagane pole!";
        blad = true;
    } else {
        bladImieObj.textContent = "";
    }

    // WIADOMOSC
    if (wiadomosc === "") {
        bladWiadomoscObj.textContent = "Wymagane pole!";
        blad = true;
    } else {
        bladWiadomoscObj.textContent = "";
    }

    // EMAIL
    if (!emailObj.checkValidity()) {
        bladEmailObj.textContent = "Nieporawny email!";
        blad = true;
    } else {
        bladEmailObj.textContent = "";
    }

    //
    if (!blad) { wyslij(imie, email, wiadomosc); }
});