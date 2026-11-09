const FORM = document.querySelector("form");
const NAME = document.querySelector("#name");
const MESSAGE = document.querySelector("#message");


FORM.addEventListener("submit", e => {
    const name = NAME.value.trim();
    const message = MESSAGE.value.trim();

    if (name === "") {
        alert("Podaj imię!")
        e.preventDefault();
        return;
     }

     if (message === "") {
        alert("Podaj wiadomość!")
        e.preventDefault();
        return;
     }    
});