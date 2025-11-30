-- ========================================
-- DATABASE FIX / CREATE TABLES FOR MedSense
-- ========================================

-- Create `user` table
CREATE TABLE IF NOT EXISTS `user` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `username` VARCHAR(50) NOT NULL UNIQUE,
    `email` VARCHAR(100) NOT NULL UNIQUE,
    `password` VARCHAR(255) NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create `reclamation` table
CREATE TABLE IF NOT EXISTS `reclamation` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `titre` VARCHAR(255) NOT NULL,
    `description` TEXT NOT NULL,
    `date` DATETIME NOT NULL,
    `id_user` INT UNSIGNED NOT NULL,
    `type` ENUM('normal', 'urgence') DEFAULT 'normal',
    `statut` ENUM('ouvert', 'en cours', 'fermé') DEFAULT 'ouvert',
    FOREIGN KEY (`id_user`) REFERENCES `user`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create `reponse` table
CREATE TABLE IF NOT EXISTS `reponse` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `contenu` TEXT NOT NULL,
    `date` DATETIME NOT NULL,
    `id_reclamation` INT UNSIGNED NOT NULL,
    `id_user` INT UNSIGNED NOT NULL,
    FOREIGN KEY (`id_reclamation`) REFERENCES `reclamation`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`id_user`) REFERENCES `user`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert a default admin user
INSERT INTO `user` (`username`, `email`, `password`) 
VALUES ('admin', 'admin@medsense.com', 
        '$2y$10$ZC9mU1ivD6K9ydp1/DXoAuX8r/Zz0R4n9wbxZ3xTVxUq.fA8w2a2G'); 
-- password = 'admin123'

