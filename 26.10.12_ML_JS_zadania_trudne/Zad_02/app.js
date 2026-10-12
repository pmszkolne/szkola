const searchBar = document.querySelector("#search");
const itemsObj = document.querySelector("#items");

function debounce(func, delay) {
    let timeout;

    // returns a new function, which delays original by 400ms
    return function(...args) {
        clearTimeout(timeout); // abort previous, if hasn't executed yet
        timeout = setTimeout(() => { func(...args); }, delay);
    };
}

function RenderItems(items) {
    // create list
    const fragment = document.createDocumentFragment();

    for (item of items) {
        const li = document.createElement("li");
        li.textContent = item;
        
        fragment.append(li);
    } 

    // replace
    itemsObj.replaceChildren(fragment);
}

let controller;
async function search(query) {
    // abort previous
    if (controller) { controller.abort(); }
    controller = new AbortController;

    // request
    try {
        // fetch
        // console.log(`/Zad_02/dane.php?q=${encodeURIComponent(query)}`)
        const response = await fetch(
            `/Zad_02/dane.php?q=${ encodeURIComponent(query) }`, 
            {signal: controller.signal}
        );
        if (!response.ok) { throw new Error ("No response!"); }
        
        const data = await response.json();

        // render
        RenderItems(data);
    } catch (e) {
        console.log(e);
    }
}
const debouncedSearch = debounce(search, 400);

//const debouncedPrint = debounce(() => { console.log("Hi"); }, 400);

searchBar.addEventListener("input", () => {
    const query = searchBar.value.trim();
    debouncedSearch(query);
});