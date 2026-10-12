const elements = [...document.querySelectorAll("#lista li")];
const searchBar = document.querySelector("#searchbar");

searchBar.addEventListener("input", () => {
    const query = searchBar.value.toLowerCase();

    elements.forEach(element => {
        const match = element.textContent.toLowerCase().includes(query)
        element.style.display = match ? "" : "none" // if match -> display; else -> hide
    });
})