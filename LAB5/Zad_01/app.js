const submitObj = document.querySelector("#submit");
const formularz = document.querySelector("form");

const imieObj = document.querySelector("#imie");
const wiadomoscObj = document.querySelector("#wiadomosc");
const znakiObj = document.querySelector("#znaki");
const zgodaObj = document.querySelector("#zgoda");
const emailObj = document.querySelector("#email");

const bladImieObj = document.querySelector("#bladImie");
const zgodaBladObj = document.querySelector("#bladZgoda");
const emailBladObj = document.querySelector("#bladEmail");
const wiadomoscBladObj = document.querySelector("#bladWiadomosc");

const witajObj = document.querySelector("#witaj");

formularz.addEventListener("submit", (e) => {
    console.log("Test");

    // imie -> conajmniej 2 znaki
    const imie = imieObj.value.trim();
    
    if (imie.length <= 1) {
        bladImieObj.textContent = "Imie musi mieć conajmniej 2 znaki!";
        e.preventDefault();
        // return;
    } else { 
        bladImieObj.textContent = ""; 
    }

    // wiadomosc -> conajmniej znak
    const wiadomosc = wiadomoscObj.value.trim();

    if (wiadomosc === "") {
        wiadomoscBladObj.textContent = "Wymagana wiadomosc!"
        event.preventDefault();
    } else {
        wiadomoscBladObj.textContent = ""
    }

    // email -> poprawny
    if (! emailObj.checkValidity()) {
        emailBladObj.textContent = "Nieporawny email!";
        event.preventDefault();
    } else {
        emailBladObj.textContent = "";
    }

    // checked
    if (!zgodaObj.checked) {
        zgodaBladObj.textContent = "Wymagana zgoda!";
        event.preventDefault();
    } else {
        zgodaBladObj.textContent = "";
    }

    // witaj
    witajObj.textContent = "Witaj " + imie + "!";
});

wiadomoscObj.addEventListener("input", () => {
    const znaki = wiadomoscObj.value.length;
    const maxLength = wiadomoscObj.maxLength;

    znakiObj.textContent = "Wpisanych znaków: " + znaki + ", pozostało: " + (maxLength - znaki);
});