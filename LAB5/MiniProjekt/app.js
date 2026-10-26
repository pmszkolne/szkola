const submitObj = document.querySelector("#submit");

const imieObj = document.querySelector("#imie");
const wiadomoscObj = document.querySelector("#wiadomosc");
const emailObj = document.querySelector("#email");
const znakiObj = document.querySelector("#znaki");

const formularz = document.querySelector("form");

async function wyslij(imie, email, wiadomosc) {
    const odpowiedz = await fetch("/MiniProjekt/odpowiedz.php", {
        method: "POST",
        headers: {
            "Content-Type": "application/json"
        },
        body: JSON.stringify({
            imie: imie,
            email: email,
            wiadomosc: wiadomosc
        })
    });

    const dane = await odpowiedz.json();

    if (!odpowiedz.ok) {
        alert("Wystąpił błąd!");
        return;
    }
}

formularz.addEventListener("submit", (e) => {
    const imie = imieObj.value;
    const email = emailObj.value;
    const wiadomosc = wiadomoscObj.value;

    wyslij(imie, email, wiadomosc);
});

wiadomoscObj.addEventListener("input", () => {
    const znaki = wiadomoscObj.value.length
    const maxLength = wiadomoscObj.maxLength

    znakiObj.textContent = "Wpisanych znaków: " + znaki + ", pozostało: " + (maxLength - znaki);
});