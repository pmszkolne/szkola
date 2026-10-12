const lista = document.querySelector("#lista");
// const elements = [...document.querySelectorAll("#lista li")];

let tasks = []
const searchBar = document.querySelector("#searchbar");

const sortAZ = document.querySelector("#sortAZ");
const sortZA = document.querySelector("#sortZA");

function getTasks() {
    return [...lista.children].map(v => v.textContent);
}

function addTask(tekst) {
    if (!tekst) {return;}

    const li = document.createElement("li");
    li.textContent = tekst;

    lista.appendChild(li);
}

function refresh() {
    lista.innerHTML = "";

    for (task of tasks) { addTask(task); }
}

function loadData() {
    // get
    let dane = JSON.parse( localStorage.getItem("zadania") ) || [];

    // load
    for (let task of dane) { addTask(task); }
}

function saveData() {
    // get data
    let dane = getTasks();

    // save data
    localStorage.setItem("zadania", JSON.stringify(dane));
}

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
    saveData();
});

sortZA.addEventListener("click", () => {
    tasks = getTasks().sort((a,b) => b.localeCompare(a));

    refresh();
    saveData();
});


///
loadData();