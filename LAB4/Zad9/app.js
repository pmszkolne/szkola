const lista = document.querySelector("#lista");
const elements = [...document.querySelectorAll("#lista li")];
const searchBar = document.querySelector("#searchbar");

const sortAZ = document.querySelector("#sortAZ");
const sortZA = document.querySelector("#sortZA");

function refresh() {
    lista.innerHTML = "";

    elements.forEach(element => {
        lista.appendChild(element);
    });
}

searchBar.addEventListener("input", () => {
    const query = searchBar.value.toLowerCase();

    elements.forEach(element => {
        const match = element.textContent.toLowerCase().includes(query);
        element.style.display = match ? "" : "none" // if match -> display; else -> hide
    });
});

sortAZ.addEventListener("click", () => {
    elements.sort((a,b) => a.textContent.localeCompare(b.textContent) );
    refresh();
});

sortZA.addEventListener("click", () => {
    elements.sort((a,b) => b.textContent.localeCompare(a.textContent) );
    refresh();
});