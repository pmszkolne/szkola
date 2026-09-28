const przycisk = document.getElementById("przycisk");
const tekst = document.getElementById("tekst");

przycisk.addEventListener("click", function() {
    tekst.textContent = "Ale teraz ten opis jest jeszcze ciekawszy, no no";
});