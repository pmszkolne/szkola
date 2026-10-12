const lista = document.querySelector("#lista");

// function render(items) {
//     for (item of items) {
//         const newItem = document.createElement("li");
//         newItem.textContent = item;

//         lista.append(newItem);
//     }
// }

let tasks = []
const searchBar = document.querySelector("#searchbar");

const sortAZ = document.querySelector("#sortAZ");
const sortZA = document.querySelector("#sortZA");

// Get current list elements
function getTasks() {
    return [...lista.children].map(v => v.textContent);
}

// Create a list element
function addTask(tekst) {
    if (!tekst) {return;}

    const li = document.createElement("li");
    li.textContent = tekst;
    li.classList.toggle("aktywne")

    const button = document.createElement("button");
    button.textContent = "Usuń"

    li.appendChild(button);
    lista.appendChild(li);
}

// Refresh list
function refresh() {
    lista.innerHTML = "";

    for (task of tasks) { addTask(task); }
}

// Query data
async function pobierzDane(params) {
    try {
        // fetch
        const response = await fetch(window.location.pathname + "/dane.php");
        if (!response.ok) { throw new Error ("Brak odpowiedzi z dane.php!"); }
        
        const dane = await response.json();

        // render
        for (task of dane) { addTask(task); }
        //render(dane)
        
    } catch (e) {
        console.log(e);
    }
}
pobierzDane();

// Searchbar
searchBar.addEventListener("input", () => {
    const query = searchBar.value.toLowerCase();

    for (const element of lista.children) {
        const match = element.textContent.toLowerCase().includes(query);
        element.style.display = match ? "" : "none" // if match -> display; else -> hide
    }
});

// Sorting
sortAZ.addEventListener("click", () => {
    tasks = getTasks().sort((a,b) => a.localeCompare(b));

    refresh();
});

sortZA.addEventListener("click", () => {
    tasks = getTasks().sort((a,b) => b.localeCompare(a));

    refresh();
});

// Changing, deleting tasks
lista.addEventListener("click", e => {
    if (e.target.matches("button")) {
        e.target.closest("li").remove();

    } else if (e.target.matches("li")) {
        e.target.classList.toggle("wykonane");
        e.target.classList.toggle("aktywne");
    }
});