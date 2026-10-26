const submitObj = document.querySelector("#submit");
const imieObj = document.querySelector("#imie");
const witajObj = document.querySelector("#witaj");
const wiadomoscObj = document.querySelector("#wiadomosc");
const znakiObj = document.querySelector("#znaki");
const bladImieObj = document.querySelector("#bladImie");
const zgodaObj = document.querySelector("#zgoda");
const zgodaBladObj = document.querySelector("#bladZgoda");
const emailObj = document.querySelector("#email");
const emailBladObj = document.querySelector("#bladEmail");

const formularz = document.querySelector("form");

formularz.addEventListener("submit", (e) => {
    console.log("Test");

    // puste?
    const imie = imieObj.value.trim();
    if (imie === "") { return; }

    // ilosc znakow
    if (imie.length <= 1) {
        bladImieObj.textContent = "Imie musi mieć conajmniej 2 znaki!";
        e.preventDefault();
        return;
    } else { 
        bladImieObj.textContent = ""; 
    }

    // email
    if (! emailObj.checkValidity()) {
        event.preventDefault();
        bladImieObj.textContent = "Nieporawny email!";
    } else {
        bladImieObj.textContent = "";
    }

    // checked
    if (!zgodaObj.checked) {
        event.preventDefault();
        zgodaBladObj.textContent = "Wymagana zgoda!";
    } else {
        zgodaBladObj.textContent = "";
    }


    // witaj
    witajObj.textContent = "Witaj " + imie + "!";
});

wiadomoscObj.addEventListener("input", () => {
    const znaki = wiadomoscObj.value.length
    const maxLength = wiadomoscObj.maxLength

    znakiObj.textContent = "Wpisanych znaków: " + znaki + ", pozostało: " + (maxLength - znaki);
});