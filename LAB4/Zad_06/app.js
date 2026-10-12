const lista = document.querySelector("#lista");

lista.addEventListener("click", e => {
    if (e.target.matches("button")) {
        e.target.closest("li").remove();
    }
});