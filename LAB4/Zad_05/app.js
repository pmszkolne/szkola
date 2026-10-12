const lista = document.getElementById("lista");
const elementy = ["A", "B", "C", "", "D"]

function dodajElement(tekst) {
    if (!tekst) {return;}

    const li = document.createElement("li");
    li.textContent = tekst;

    lista.appendChild(li);
}

elementy.forEach(element => {
    dodajElement(element);
});