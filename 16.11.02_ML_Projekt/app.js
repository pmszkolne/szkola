document.addEventListener('DOMContentLoaded', () => {
    const form = document.querySelector('form');
    const klasaSelect = document.querySelector('#klasa');

    const updateFormBorder = () => {
        const klasa = klasaSelect.value;

        // assign class
        form.classList.remove("Wojownik");
        form.classList.remove("Mag");
        form.classList.remove("Łotrzyk");

        if (["Wojownik", "Mag", "Łotrzyk"].includes(klasa)) { // prevents appending incorrect classes
            form.classList.add(klasa);
        }
    };

    klasaSelect.addEventListener('change', updateFormBorder);
    updateFormBorder();
});