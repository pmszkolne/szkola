-- Baza danych (Smart Office SPA)

CREATE DATABASE IF NOT EXISTS `smart_office`
  DEFAULT CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE `smart_office`;

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS `reservations`;
DROP TABLE IF EXISTS `resources`;

-- Tabela zasobów biurowych (biurka oraz sale konferencyjne)
CREATE TABLE `resources` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` ENUM('desk', 'room') COLLATE utf8mb4_unicode_ci NOT NULL,
  `capacity` INT NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela rezerwacji powiązana kluczem obcym z tabelą resources
CREATE TABLE `reservations` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `resource_id` INT NOT NULL,
  `user_name` VARCHAR(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `start_time` DATETIME NOT NULL,
  `end_time` DATETIME NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_resource_time` (`resource_id`, `start_time`, `end_time`),
  CONSTRAINT `fk_reservations_resource`
    FOREIGN KEY (`resource_id`) REFERENCES `resources` (`id`)
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;

-- Dane startowe: 8 biurek (desk) oraz 2 sale konferencyjne (room)
INSERT INTO `resources` (`id`, `name`, `type`, `capacity`) VALUES
(1, 'Biurko A1', 'desk', 1),
(2, 'Biurko A2', 'desk', 1),
(3, 'Biurko A3', 'desk', 1),
(4, 'Biurko A4', 'desk', 1),
(5, 'Biurko B1', 'desk', 1),
(6, 'Biurko B2', 'desk', 1),
(7, 'Biurko B3', 'desk', 1),
(8, 'Biurko B4', 'desk', 1),
(9, 'Sala Konferencyjna Alfa', 'room', 8),
(10, 'Sala Konferencyjna Beta', 'room', 12);

-- Przykładowe rezerwacje startowe (relatywne względem bieżącej daty)
INSERT INTO `reservations` (`resource_id`, `user_name`, `start_time`, `end_time`) VALUES
(2, 'Anna Kowalska', DATE_ADD(CURDATE(), INTERVAL 9 HOUR), DATE_ADD(CURDATE(), INTERVAL 13 HOUR)),
(6, 'Piotr Nowak', DATE_ADD(CURDATE(), INTERVAL 10 HOUR), DATE_ADD(CURDATE(), INTERVAL 15 HOUR)),
(9, 'Zespół Projektowy IT', DATE_ADD(CURDATE(), INTERVAL 11 HOUR), DATE_ADD(CURDATE(), INTERVAL 14 HOUR)),
(1, 'Marek Wiśniewski', DATE_ADD(DATE_ADD(CURDATE(), INTERVAL 1 DAY), INTERVAL 9 HOUR), DATE_ADD(DATE_ADD(CURDATE(), INTERVAL 1 DAY), INTERVAL 12 HOUR)),
(10, 'Zarząd Spółki', DATE_ADD(DATE_ADD(CURDATE(), INTERVAL 1 DAY), INTERVAL 10 HOUR), DATE_ADD(DATE_ADD(CURDATE(), INTERVAL 1 DAY), INTERVAL 12 HOUR));
