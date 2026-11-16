
(() => {
  'use strict';

  const API_URL = 'api/reservations.php';
  const THEME_STORAGE_KEY = 'smart_office_theme';

  // Stan aplikacji
  const state = {
    resources: [],
    reservations: [],
    selectedResourceId: null,
  };

  // Elementy DOM
  const elements = {
    themeToggle: document.getElementById('themeToggle'),
    themeIcon: document.getElementById('themeIcon'),
    themeLabel: document.getElementById('themeLabel'),

    filterDate: document.getElementById('filterDate'),
    filterStart: document.getElementById('filterStart'),
    filterEnd: document.getElementById('filterEnd'),
    refreshBtn: document.getElementById('refreshBtn'),
    filterValidationMsg: document.getElementById('filterValidationMsg'),
    activeRangeBadge: document.getElementById('activeRangeBadge'),

    icsBanner: document.getElementById('icsBanner'),
    icsBannerDetails: document.getElementById('icsBannerDetails'),
    icsDownloadLink: document.getElementById('icsDownloadLink'),
    closeIcsBanner: document.getElementById('closeIcsBanner'),

    officeGrid: document.getElementById('officeGrid'),
    reservationsTableBody: document.getElementById('reservationsTableBody'),
    reservationsCount: document.getElementById('reservationsCount'),

    bookingModal: document.getElementById('bookingModal'),
    bookingForm: document.getElementById('bookingForm'),
    closeModalBtn: document.getElementById('closeModalBtn'),
    cancelModalBtn: document.getElementById('cancelModalBtn'),
    modalResourceId: document.getElementById('modalResourceId'),
    modalResourceTypeBadge: document.getElementById('modalResourceTypeBadge'),
    modalTitle: document.getElementById('modalTitle'),
    modalResourceName: document.getElementById('modalResourceName'),
    modalResourceCapacity: document.getElementById('modalResourceCapacity'),
    modalDateTimeSummary: document.getElementById('modalDateTimeSummary'),
    userNameInput: document.getElementById('userNameInput'),
    modalErrorMsg: document.getElementById('modalErrorMsg'),
    submitBookingBtn: document.getElementById('submitBookingBtn'),

    toastContainer: document.getElementById('toastContainer'),
  };

  /* =========================================================
     1. Motyw jasny / ciemny (Light / Dark Mode + localStorage)
     ========================================================= */
  function applyTheme(theme) {
    const isDark = theme === 'dark';
    document.documentElement.setAttribute('data-theme', isDark ? 'dark' : 'light');
    elements.themeIcon.textContent = isDark ? '☀️' : '🌙';
    elements.themeLabel.textContent = isDark ? 'Tryb jasny' : 'Tryb ciemny';
    localStorage.setItem(THEME_STORAGE_KEY, isDark ? 'dark' : 'light');
  }

  function initTheme() {
    const savedTheme = localStorage.getItem(THEME_STORAGE_KEY);
    if (savedTheme === 'dark' || savedTheme === 'light') {
      applyTheme(savedTheme);
    } else {
      const prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
      applyTheme(prefersDark ? 'dark' : 'light');
    }

    elements.themeToggle.addEventListener('click', () => {
      const current = document.documentElement.getAttribute('data-theme');
      applyTheme(current === 'dark' ? 'light' : 'dark');
    });
  }

  /* =========================================================
     2. Pomocnicze funkcje daty i czasu
     ========================================================= */
  function formatDateLocal(dateObj) {
    const year = dateObj.getFullYear();
    const month = String(dateObj.getMonth() + 1).padStart(2, '0');
    const day = String(dateObj.getDate()).padStart(2, '0');
    return `${year}-${month}-${day}`;
  }

  function initDefaultDateTime() {
    const now = new Date();
    const todayStr = formatDateLocal(now);

    // Blokada wyboru dat z przeszłości w kontrolce HTML
    elements.filterDate.min = todayStr;

    let defaultDate = new Date(now);
    let startHour = now.getHours() + 1;

    // Jeśli jest po 22:00, ustaw domyślnie jutro 09:00 - 10:00
    if (startHour >= 23) {
      defaultDate.setDate(defaultDate.getDate() + 1);
      startHour = 9;
    }

    const endHour = startHour + 1;
    elements.filterDate.value = formatDateLocal(defaultDate);
    elements.filterStart.value = `${String(startHour).padStart(2, '0')}:00`;
    elements.filterEnd.value = `${String(endHour).padStart(2, '0')}:00`;
  }

  function getSelectedRange() {
    const date = elements.filterDate.value;
    const start = elements.filterStart.value;
    const end = elements.filterEnd.value;

    return {
      date,
      start,
      end,
      startDateTime: date && start ? `${date} ${start}` : '',
      endDateTime: date && end ? `${date} ${end}` : '',
    };
  }

  function validateTimeRange({ date, start, end }, allowPastForPreview = false) {
    const todayStr = formatDateLocal(new Date());
    elements.filterDate.min = todayStr;

    if (!date || !start || !end) {
      return 'Wypełnij datę oraz godziny rozpoczęcia i zakończenia.';
    }

    if (date < todayStr) {
      return 'Nie można wybrać daty z przeszłości.';
    }

    if (end <= start) {
      return 'Godzina zakończenia musi być późniejsza niż godzina rozpoczęcia.';
    }

    if (!allowPastForPreview) {
      const startObj = new Date(`${date}T${start}:00`);
      const nowMinusGrace = new Date(Date.now() - 60 * 1000);
      if (startObj < nowMinusGrace) {
        return 'Wybrana godzina rozpoczęcia już minęła. Wybierz przyszły termin.';
      }
    }

    return null;
  }

  /* =========================================================
     3. Powiadomienia Toast
     ========================================================= */
  function showToast(title, message, type = 'info', icsReservationId = null) {
    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;

    const header = document.createElement('div');
    header.className = 'toast-header';

    const titleEl = document.createElement('span');
    titleEl.textContent = title;

    const closeBtn = document.createElement('button');
    closeBtn.type = 'button';
    closeBtn.className = 'toast-close';
    closeBtn.setAttribute('aria-label', 'Zamknij powiadomienie');
    closeBtn.textContent = '×';

    header.append(titleEl, closeBtn);

    const body = document.createElement('div');
    body.className = 'toast-body';
    body.textContent = message;

    toast.append(header, body);

    if (icsReservationId) {
      const icsLink = document.createElement('a');
      icsLink.href = `${API_URL}?action=ics&id=${encodeURIComponent(icsReservationId)}`;
      icsLink.className = 'btn btn-success btn-sm';
      icsLink.setAttribute('download', 'rezerwacja.ics');
      icsLink.textContent = '📅 Pobierz plik kalendarza (.ics)';
      toast.appendChild(icsLink);
    }

    const removeToast = () => {
      toast.classList.add('toast-fade-out');
      setTimeout(() => toast.remove(), 250);
    };

    closeBtn.addEventListener('click', removeToast);
    elements.toastContainer.appendChild(toast);

    const duration = icsReservationId ? 9000 : 4500;
    setTimeout(() => {
      if (toast.isConnected) {
        removeToast();
      }
    }, duration);
  }

  /* =========================================================
     4. Komunikacja z API (GET, POST, DELETE)
     ========================================================= */
  async function loadOfficeData() {
    const range = getSelectedRange();
    const error = validateTimeRange(range, true);

    if (error) {
      elements.filterValidationMsg.textContent = error;
      elements.filterValidationMsg.hidden = false;
      elements.activeRangeBadge.textContent = 'Nieprawidłowy zakres';
      return;
    }

    elements.filterValidationMsg.hidden = true;
    elements.activeRangeBadge.textContent = `${range.date} | ${range.start} – ${range.end}`;

    try {
      const query = new URLSearchParams({
        start: range.startDateTime,
        end: range.endDateTime,
      });

      const response = await fetch(`${API_URL}?${query.toString()}`, {
        method: 'GET',
        headers: { Accept: 'application/json' },
      });

      const payload = await response.json();

      if (!response.ok) {
        throw new Error(payload.message || 'Błąd pobierania danych z serwera.');
      }

      state.resources = Array.isArray(payload.resources) ? payload.resources : [];
      state.reservations = Array.isArray(payload.reservations) ? payload.reservations : [];

      renderOfficeGrid();
      renderReservationsTable();
    } catch (err) {
      showToast('Błąd komunikacji', err.message, 'error');
    }
  }

  async function createReservation(resourceId, userName, startDateTime, endDateTime) {
    elements.submitBookingBtn.disabled = true;
    elements.submitBookingBtn.textContent = 'Zapisywanie...';

    try {
      const response = await fetch(API_URL, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          Accept: 'application/json',
        },
        body: JSON.stringify({
          resource_id: resourceId,
          user_name: userName,
          start_time: startDateTime,
          end_time: endDateTime,
        }),
      });

      const payload = await response.json();

      if (!response.ok) {
        throw new Error(payload.message || 'Nie udało się zapisać rezerwacji.');
      }

      const created = payload.reservation || {};
      closeBookingModal();

      // Wyświetlenie banera oraz powiadomienia z przyciskiem pobrania pliku .ics
      if (created.id) {
        elements.icsBannerDetails.textContent = `${created.resource_name} (${created.start_time} – ${created.end_time}) dla: ${userName}`;
        elements.icsDownloadLink.href = `${API_URL}?action=ics&id=${encodeURIComponent(created.id)}`;
        elements.icsBanner.hidden = false;
      }

      showToast(
        'Sukces',
        payload.message || 'Rezerwacja została utworzona.',
        'success',
        created.id || null
      );

      await loadOfficeData();
    } catch (err) {
      elements.modalErrorMsg.textContent = err.message;
      elements.modalErrorMsg.hidden = false;
      showToast('Błąd rezerwacji', err.message, 'error');
    } finally {
      elements.submitBookingBtn.disabled = false;
      elements.submitBookingBtn.textContent = 'Potwierdź rezerwację';
    }
  }

  async function cancelReservation(reservationId) {
    const confirmed = window.confirm(
      `Czy na pewno chcesz anulować rezerwację #${reservationId}?`
    );

    if (!confirmed) {
      return;
    }

    try {
      const response = await fetch(`${API_URL}?id=${encodeURIComponent(reservationId)}`, {
        method: 'DELETE',
        headers: { Accept: 'application/json' },
      });

      const payload = await response.json();

      if (!response.ok) {
        throw new Error(payload.message || 'Nie udało się anulować rezerwacji.');
      }

      showToast('Anulowano', payload.message || 'Rezerwacja została usunięta.', 'success');
      await loadOfficeData();
    } catch (err) {
      showToast('Błąd usuwania', err.message, 'error');
    }
  }

  /* =========================================================
     5. Renderowanie rzutu biura (CSS Grid) i listy rezerwacji
     ========================================================= */
  function renderOfficeGrid() {
    elements.officeGrid.innerHTML = '';

    if (state.resources.length === 0) {
      const empty = document.createElement('p');
      empty.className = 'empty-state';
      empty.textContent = 'Brak zdefiniowanych stanowisk w bazie danych.';
      elements.officeGrid.appendChild(empty);
      return;
    }

    state.resources.forEach((resource) => {
      const isOccupied = Boolean(resource.is_occupied);
      const isSelected = state.selectedResourceId === resource.id && !isOccupied;

      const card = document.createElement('div');
      card.setAttribute('role', 'gridcell');
      card.tabIndex = isOccupied ? -1 : 0;

      const typeClass = resource.type === 'room' ? 'resource-room' : 'resource-desk';
      let statusClass = 'status-free';
      let statusText = 'Wolne';

      if (isOccupied) {
        statusClass = 'status-occupied';
        statusText = 'Zajęte';
      } else if (isSelected) {
        statusClass = 'status-selected';
        statusText = 'Wybrane';
      }

      card.className = `resource-card ${typeClass} ${statusClass}`;

      const topRow = document.createElement('div');
      topRow.className = 'resource-top';

      const kindSpan = document.createElement('span');
      kindSpan.className = 'resource-kind';
      kindSpan.textContent = resource.type === 'room' ? 'Sala konf.' : 'Biurko';

      const statusBadge = document.createElement('span');
      statusBadge.className = 'resource-status-pill';
      statusBadge.textContent = statusText;

      topRow.append(kindSpan, statusBadge);

      const nameEl = document.createElement('div');
      nameEl.className = 'resource-name';
      nameEl.textContent = resource.name;

      const metaEl = document.createElement('div');
      metaEl.className = 'resource-meta';
      metaEl.textContent =
        resource.type === 'room'
          ? `Pojemność: ${resource.capacity} os.`
          : `Stanowisko ${resource.capacity}-osobowe`;

      card.append(topRow, nameEl, metaEl);

      if (!isOccupied) {
        const handleSelect = () => openBookingModal(resource);
        card.addEventListener('click', handleSelect);
        card.addEventListener('keydown', (e) => {
          if (e.key === 'Enter' || e.key === ' ') {
            e.preventDefault();
            handleSelect();
          }
        });
      }

      elements.officeGrid.appendChild(card);
    });
  }

  function decodeHtmlEntities(str) {
    const txt = document.createElement('textarea');
    txt.innerHTML = str;
    return txt.value;
  }

  function renderReservationsTable() {
    elements.reservationsTableBody.innerHTML = '';
    elements.reservationsCount.textContent = `${state.reservations.length} rezerwacji`;

    if (state.reservations.length === 0) {
      const tr = document.createElement('tr');
      const td = document.createElement('td');
      td.colSpan = 7;
      td.className = 'empty-state';
      td.textContent = 'Brak aktywnych rezerwacji w systemie.';
      tr.appendChild(td);
      elements.reservationsTableBody.appendChild(tr);
      return;
    }

    state.reservations.forEach((res) => {
      const tr = document.createElement('tr');

      const tdId = document.createElement('td');
      tdId.textContent = `#${res.id}`;

      const tdName = document.createElement('td');
      const strongName = document.createElement('strong');
      strongName.textContent = res.resource_name;
      tdName.appendChild(strongName);

      const tdType = document.createElement('td');
      tdType.textContent = res.resource_type === 'room' ? 'Sala konferencyjna' : 'Biurko';

      const tdUser = document.createElement('td');
      tdUser.textContent = decodeHtmlEntities(String(res.user_name));

      const tdStart = document.createElement('td');
      tdStart.textContent = res.start_time;

      const tdEnd = document.createElement('td');
      tdEnd.textContent = res.end_time;

      const tdActions = document.createElement('td');
      const actionGroup = document.createElement('div');
      actionGroup.className = 'action-group';

      const icsBtn = document.createElement('a');
      icsBtn.href = `${API_URL}?action=ics&id=${encodeURIComponent(res.id)}`;
      icsBtn.className = 'btn btn-outline btn-sm';
      icsBtn.setAttribute('download', 'rezerwacja.ics');
      icsBtn.title = 'Pobierz plik kalendarza .ics';
      icsBtn.textContent = '.ICS';

      const deleteBtn = document.createElement('button');
      deleteBtn.type = 'button';
      deleteBtn.className = 'btn btn-danger btn-sm';
      deleteBtn.textContent = 'Anuluj';
      deleteBtn.addEventListener('click', () => cancelReservation(res.id));

      actionGroup.append(icsBtn, deleteBtn);
      tdActions.appendChild(actionGroup);

      tr.append(tdId, tdName, tdType, tdUser, tdStart, tdEnd, tdActions);
      elements.reservationsTableBody.appendChild(tr);
    });
  }

  /* =========================================================
     6. Okno modalne i walidacja formularza
     ========================================================= */
  function openBookingModal(resource) {
    const range = getSelectedRange();
    const rangeError = validateTimeRange(range, false);

    if (rangeError) {
      elements.filterValidationMsg.textContent = rangeError;
      elements.filterValidationMsg.hidden = false;
      showToast('Nieprawidłowy termin', rangeError, 'error');
      return;
    }

    state.selectedResourceId = resource.id;
    renderOfficeGrid();

    elements.modalResourceId.value = String(resource.id);
    elements.modalResourceTypeBadge.textContent =
      resource.type === 'room' ? 'Sala konferencyjna' : 'Biurko';
    elements.modalTitle.textContent = `Rezerwacja: ${resource.name}`;
    elements.modalResourceName.textContent = resource.name;
    elements.modalResourceCapacity.textContent = `${resource.capacity} os.`;
    elements.modalDateTimeSummary.textContent = `${range.date}, ${range.start} – ${range.end}`;

    elements.modalErrorMsg.hidden = true;
    elements.bookingModal.hidden = false;
    elements.userNameInput.focus();
  }

  function closeBookingModal() {
    elements.bookingModal.hidden = true;
    elements.bookingForm.reset();
    elements.modalErrorMsg.hidden = true;
    state.selectedResourceId = null;
    renderOfficeGrid();
  }

  function bindEvents() {
    // Dynamiczne odświeżanie dostępności przy zmianie daty lub godzin
    [elements.filterDate, elements.filterStart, elements.filterEnd].forEach((input) => {
      input.addEventListener('change', () => {
        loadOfficeData();
      });
    });

    elements.refreshBtn.addEventListener('click', () => {
      loadOfficeData();
    });

    // Zamykanie okna modalnego
    elements.closeModalBtn.addEventListener('click', closeBookingModal);
    elements.cancelModalBtn.addEventListener('click', closeBookingModal);
    elements.bookingModal.addEventListener('click', (e) => {
      if (e.target === elements.bookingModal) {
        closeBookingModal();
      }
    });

    document.addEventListener('keydown', (e) => {
      if (e.key === 'Escape' && !elements.bookingModal.hidden) {
        closeBookingModal();
      }
    });

    // Zamykanie banera .ics
    elements.closeIcsBanner.addEventListener('click', () => {
      elements.icsBanner.hidden = true;
    });

    // Walidacja i wysyłka formularza rezerwacji
    elements.bookingForm.addEventListener('submit', async (e) => {
      e.preventDefault();
      elements.modalErrorMsg.hidden = true;

      const resourceId = Number(elements.modalResourceId.value);
      const userName = elements.userNameInput.value.trim();
      const range = getSelectedRange();

      // Walidacja po stronie klienta
      const rangeError = validateTimeRange(range, false);
      if (rangeError) {
        elements.modalErrorMsg.textContent = rangeError;
        elements.modalErrorMsg.hidden = false;
        return;
      }

      if (userName.length < 3) {
        elements.modalErrorMsg.textContent = 'Imię i nazwisko musi zawierać co najmniej 3 znaki.';
        elements.modalErrorMsg.hidden = false;
        elements.userNameInput.focus();
        return;
      }

      await createReservation(
        resourceId,
        userName,
        range.startDateTime,
        range.endDateTime
      );
    });
  }

  // Inicjalizacja aplikacji SPA
  document.addEventListener('DOMContentLoaded', () => {
    initTheme();
    initDefaultDateTime();
    bindEvents();
    loadOfficeData();
  });
})();
