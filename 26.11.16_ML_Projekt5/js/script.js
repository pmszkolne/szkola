
document.addEventListener('DOMContentLoaded', () => {
    // 1. Dynamiczny licznik znaków dla pola opisu (max 300)
    const descriptionInput = document.getElementById('description');
    const charCountSpan = document.getElementById('char-count');
    const charCounterBox = document.getElementById('char-counter');
    const MAX_DESC_LENGTH = 300;

    const updateCharCounter = () => {
        if (!descriptionInput || !charCountSpan || !charCounterBox) return;
        const length = descriptionInput.value.length;
        charCountSpan.textContent = String(length);

        charCounterBox.classList.remove('warning', 'limit');
        if (length >= MAX_DESC_LENGTH) {
            charCounterBox.classList.add('limit');
        } else if (length >= 250) {
            charCounterBox.classList.add('warning');
        }
    };

    if (descriptionInput) {
        updateCharCounter();
        descriptionInput.addEventListener('input', updateCharCounter);
    }

    // 2. Walidacja formularza przed wysłaniem
    const ticketForm = document.getElementById('ticket-form');
    const roomInput = document.getElementById('room');
    const titleInput = document.getElementById('title');
    const formErrorsBox = document.getElementById('form-errors');

    if (ticketForm) {
        ticketForm.addEventListener('submit', (event) => {
            const errors = [];
            const roomVal = roomInput ? roomInput.value.trim() : '';
            const titleVal = titleInput ? titleInput.value.trim() : '';
            const descVal = descriptionInput ? descriptionInput.value.trim() : '';
            const descRawLen = descriptionInput ? descriptionInput.value.length : 0;

            if (roomVal === '') {
                errors.push('Podaj numer sali lub lokalizację usterki.');
            }
            if (titleVal.length < 5) {
                errors.push('Tytuł zgłoszenia musi zawierać co najmniej 5 znaków.');
            }
            if (descVal === '') {
                errors.push('Opis usterki nie może być pusty.');
            } else if (descRawLen > MAX_DESC_LENGTH) {
                errors.push(`Opis usterki może zawierać maksymalnie ${MAX_DESC_LENGTH} znaków.`);
            }

            if (errors.length > 0) {
                event.preventDefault();
                if (formErrorsBox) {
                    const listItems = errors.map((msg) => `<li>${msg}</li>`).join('');
                    formErrorsBox.innerHTML = `<ul>${listItems}</ul>`;
                    formErrorsBox.hidden = false;
                }
            } else if (formErrorsBox) {
                formErrorsBox.hidden = true;
                formErrorsBox.innerHTML = '';
            }
        });
    }

    // 3. Filtrowanie po statusie i wyszukiwanie tekstowe na żywo (DOM)
    const searchInput = document.getElementById('search-input');
    const filterButtons = document.querySelectorAll('.filter-btn');
    const ticketCards = document.querySelectorAll('.ticket-card');
    const noResultsBox = document.getElementById('no-results');

    let activeStatus = 'all';
    let searchQuery = '';

    const applyFilters = () => {
        if (ticketCards.length === 0) return;

        let visibleCount = 0;

        ticketCards.forEach((card) => {
            const cardStatus = card.dataset.status || '';
            const cardText = card.textContent.toLowerCase();

            const matchesStatus = activeStatus === 'all' || cardStatus === activeStatus;
            const matchesSearch = searchQuery === '' || cardText.includes(searchQuery);

            if (matchesStatus && matchesSearch) {
                card.hidden = false;
                card.style.display = '';
                visibleCount++;
            } else {
                card.hidden = true;
                card.style.display = 'none';
            }
        });

        if (noResultsBox) {
            noResultsBox.hidden = visibleCount > 0;
        }
    };

    filterButtons.forEach((btn) => {
        btn.addEventListener('click', () => {
            filterButtons.forEach((b) => b.classList.remove('active'));
            btn.classList.add('active');
            activeStatus = btn.dataset.filter || 'all';
            applyFilters();
        });
    });

    if (searchInput) {
        searchInput.addEventListener('input', () => {
            searchQuery = searchInput.value.trim().toLowerCase();
            applyFilters();
        });
    }

    // 4. Potwierdzenie usunięcia zamkniętego zgłoszenia (confirm)
    const deleteForms = document.querySelectorAll('.delete-form');
    deleteForms.forEach((form) => {
        form.addEventListener('submit', (event) => {
            const confirmed = confirm(
                'Czy na pewno chcesz trwale usunąć to zamknięte zgłoszenie?'
            );
            if (!confirmed) {
                event.preventDefault();
            }
        });
    });
});
