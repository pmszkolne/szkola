-- Baza danych

CREATE DATABASE IF NOT EXISTS helpdesk_db
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE helpdesk_db;

-- Usunięcie tabel w kolejności uwzględniającej klucz obcy
DROP TABLE IF EXISTS tickets;
DROP TABLE IF EXISTS categories;

-- Tabela kategorii usterek
CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(60) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela zgłoszeń serwisowych
CREATE TABLE tickets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT NOT NULL,
    room VARCHAR(20) NOT NULL,
    title VARCHAR(100) NOT NULL,
    description TEXT NOT NULL,
    priority ENUM('niski', 'sredni', 'wysoki') NOT NULL DEFAULT 'sredni',
    status ENUM('nowe', 'w_trakcie', 'zamkniete') NOT NULL DEFAULT 'nowe',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_tickets_category
        FOREIGN KEY (category_id) REFERENCES categories(id)
        ON DELETE RESTRICT
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dane startowe: kategorie (minimum 4)
INSERT INTO categories (id, name) VALUES
(1, 'Sprzęt komputerowy'),
(2, 'Sieć i Wi-Fi'),
(3, 'Oprogramowanie'),
(4, 'Drukarki i peryferia'),
(5, 'Projektory i tablice');

-- Dane startowe: przykładowe zgłoszenia (minimum 5 o różnych statusach)
INSERT INTO tickets (category_id, room, title, description, priority, status, created_at) VALUES
(2, 'Sala 204', 'Brak dostępu do sieci lokalnej na stanowiskach uczniowskich', 'Komputery w rzędzie pod oknem nie pobierają adresu IP z serwera DHCP po restarcie przełącznika.', 'wysoki', 'nowe', '2026-11-16 08:15:00'),
(1, 'Sala 112', 'Komputer nauczyciela nie uruchamia się po burzy', 'Po naciśnięciu przycisku zasilania brak reakcji diod na obudowie, prawdopodobnie uszkodzony zasilacz ATX.', 'wysoki', 'w_trakcie', '2026-11-15 13:40:00'),
(3, 'Pracownia 305', 'Brak pakietu XAMPP oraz Visual Studio Code', 'Na stanowisku nr 08 po przywróceniu obrazu systemu brakuje środowiska PHP 8 oraz edytora kodu.', 'sredni', 'nowe', '2026-11-16 09:50:00'),
(4, 'Sekretariat', 'Drukarka laserowa zacina papier z podajnika głównego', 'Przy wydruku wielostronicowym arkusze zatrzymują się przy rolce pobierającej. Wymaga czyszczenia.', 'sredni', 'w_trakcie', '2026-11-14 11:20:00'),
(5, 'Sala 108', 'Zniekształcone kolory obrazu z projektora sufitowego', 'Obraz wyświetlany na tablicy ma wyraźną żółtą poświatę, prawdopodobnie poluzowany przewód HDMI.', 'niski', 'zamkniete', '2026-11-12 10:05:00'),
(1, 'Pracownia 302', 'Uszkodzona klawiatura oraz rolka myszy na stanowisku 14', 'Klawisz Enter zacina się podczas pisania, a scroll w myszce przewija stronę skokowo.', 'niski', 'zamkniete', '2026-11-10 14:30:00');
