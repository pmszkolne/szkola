const zmianaMotywu = document.querySelector('#zmienMotyw');
const innyCytat = document.querySelector('#innyCytat');

zmianaMotywu.addEventListener('click', () => {
    document.body.classList.toggle('dark-mode');
});

innyCytat.addEventListener('click', () => {
    location.reload();
});