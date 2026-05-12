-- SQL: create_as_history.sql
-- Run this in your MySQL/MariaDB to create the as_history table used by the History model.

CREATE TABLE IF NOT EXISTS `as_history` (
  `id` INT NOT NULL,
  `electric_id` VARCHAR(128) NOT NULL,
  `type` ENUM('Masuk','Keluar') NOT NULL,
  `qty` INT NOT NULL DEFAULT 0,
  `user_nik` VARCHAR(20) DEFAULT NULL,
  `keterangan` TEXT DEFAULT NULL,
  `date` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX (`electric_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
