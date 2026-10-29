const submitObj = document.querySelector("#submit");

const imieObj = document.querySelector("#imie");
const wiadomoscObj = document.querySelector("#wiadomosc");
const emailObj = document.querySelector("#email");

const znakiObj = document.querySelector("#znaki");
const bladImieObj = document.querySelector("#bladImie");
const bladWiadomoscObj = document.querySelector("#bladWiadomosc");
const bladEmailObj = document.querySelector("#bladEmail");

const formularz = document.querySelector("form");

// Wysylanie
// console.log(new URL("/MiniProjekt/odpowiedz.php", window.location.href));
async function wyslij(imie, email, wiadomosc) {
    
    
    try {
        const url = "http://127.0.0.1:8000/MiniProjekt/test.php";
        const response = await fetch(url);
        // const odpowiedz = await fetch("/MiniProjekt/odpowiedz.php", {
        //     method: "POST",
        //     headers: {"Content-Type": "application/json"},
        //     body: JSON.stringify({
        //         imie: imie,
        //         email: email,
        //         wiadomosc: wiadomosc
        //     })
        // });
        console.log("works");
    } catch (e) {
        console.error(e);
    }
    

    // const dane = await odpowiedz.json();

    // if (!odpowiedz.ok) {
    //     alert("Wystąpił błąd!");
    //     return;
    // }
}

// Walidacja
formularz.addEventListener("submit", (e) => {
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

    if (blad) {
        event.preventDefault();
        return;
    }

    wyslij(imie, email, wiadomosc);
});

// Liczenie znakow
wiadomoscObj.addEventListener("input", () => {
    const znaki = wiadomoscObj.value.length
    const maxLength = wiadomoscObj.maxLength

    znakiObj.textContent = "Wpisanych znaków: " + znaki + ", pozostało: " + (maxLength - znaki);
});