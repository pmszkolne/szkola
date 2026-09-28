const lista = [...document.querySelectorAll("li")]

lista.forEach(element => {
    element.addEventListener("click", () => {
        element.classList.toggle("aktywne")
        element.classList.toggle("wykonane")
        element.classList.remove("blad")
    });
});