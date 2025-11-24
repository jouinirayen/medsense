CREATE TABLE IF NOT EXISTS `rendezvous` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `service_id` INT UNSIGNED NOT NULL,
    `appointment_date` DATE NOT NULL,
    `appointment_time` TIME NOT NULL,
    `is_booked` TINYINT(1) NOT NULL DEFAULT 0,
    `booked_email` VARCHAR(255) DEFAULT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_service_date` (`service_id`, `appointment_date`),
    CONSTRAINT `fk_rendezvous_service`
        FOREIGN KEY (`service_id`) REFERENCES `services`(`id`)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `rendezvous` (`service_id`, `appointment_date`, `appointment_time`, `is_booked`, `booked_email`)
VALUES
    (1, '2025-11-26', '11:00:00', 0, NULL),
    (1, '2025-11-26', '14:30:00', 0, NULL),
    (1, '2025-11-28', '09:00:00', 0, NULL),
    (2, '2025-11-25', '10:00:00', 0, NULL),
    (2, '2025-11-27', '16:00:00', 0, NULL);

