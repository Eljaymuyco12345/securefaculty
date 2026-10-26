-- SecureFaculty MySQL Database Schema
-- Capstone Project: CICI Department - ISUFST Main Campus
-- Compliant with RA 10173 (Data Privacy Act of 2012)

CREATE DATABASE IF NOT EXISTS `securefaculty_db` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `securefaculty_db`;

-- 1. Users Table (One-Gate RBAC & Bcrypt Authentication)
CREATE TABLE IF NOT EXISTS `users` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `username` VARCHAR(64) NOT NULL UNIQUE,
    `password_hash` VARCHAR(255) NOT NULL,
    `role` ENUM('dean', 'faculty') NOT NULL DEFAULT 'faculty',
    `full_name` VARCHAR(150) NOT NULL,
    `email` VARCHAR(150),
    `department` VARCHAR(100) DEFAULT 'CICI',
    `is_active` TINYINT(1) DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2. Faculty Profiles Table (1:1 with Users)
CREATE TABLE IF NOT EXISTS `faculty_profiles` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL UNIQUE,
    `employee_id` VARCHAR(50),
    `academic_rank` VARCHAR(100),
    `specialization` VARCHAR(150),
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3. Certificates Table (1:M with Users)
CREATE TABLE IF NOT EXISTS `certificates` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `faculty_id` INT NOT NULL,
    `title` VARCHAR(255) NOT NULL,
    `issuer` VARCHAR(255),
    `date_issued` VARCHAR(50),
    `category` VARCHAR(100) DEFAULT 'Seminar',
    `file_path` VARCHAR(255) NOT NULL,
    `original_filename` VARCHAR(255) NOT NULL,
    `file_type` VARCHAR(10) NOT NULL,
    `file_size` INT NOT NULL,
    `file_hash` VARCHAR(64) NULL,
    `cert_number` VARCHAR(100) NULL,
    `processing_status` VARCHAR(50) DEFAULT 'completed',
    `batch_id` VARCHAR(100) NULL,
    `ocr_raw_text` MEDIUMTEXT,
    `ocr_confidence` DECIMAL(5,2) DEFAULT 0.95,
    `status` VARCHAR(50) DEFAULT 'approved',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`faculty_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 4. Certificate Authors (M:N Mapping for Research Publications & Co-authors)
CREATE TABLE IF NOT EXISTS `certificate_authors` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `certificate_id` INT NOT NULL,
    `user_id` INT NOT NULL,
    FOREIGN KEY (`certificate_id`) REFERENCES `certificates`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 5. Audit Logs Table (Immutable RA 10173 Audit Trail)
CREATE TABLE IF NOT EXISTS `audit_logs` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT,
    `username` VARCHAR(64),
    `role` VARCHAR(32),
    `action` VARCHAR(64) NOT NULL,
    `target_id` INT,
    `details` TEXT,
    `ip_address` VARCHAR(45),
    `user_agent` VARCHAR(255),
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 6. API Quotas Table (Real-Time Quota Monitor)
CREATE TABLE IF NOT EXISTS `api_quotas` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `service_name` VARCHAR(64) NOT NULL UNIQUE,
    `monthly_limit` INT DEFAULT 25000,
    `current_usage` INT DEFAULT 0,
    `month_year` VARCHAR(10) NOT NULL,
    `last_updated` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 7. Processing Batches Table (Multi-File & ZIP Ingestion Tracking)
CREATE TABLE IF NOT EXISTS `processing_batches` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `batch_uuid` VARCHAR(64) NOT NULL UNIQUE,
    `user_id` INT NOT NULL,
    `total_files` INT DEFAULT 0,
    `processed_files` INT DEFAULT 0,
    `success_files` INT DEFAULT 0,
    `failed_files` INT DEFAULT 0,
    `status` VARCHAR(32) DEFAULT 'pending',
    `current_stage` VARCHAR(64) DEFAULT 'Upload',
    `log_summary` TEXT,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 8. Performance & Query Acceleration Indexes
CREATE INDEX IF NOT EXISTS `idx_certificates_faculty` ON `certificates`(`faculty_id`);
CREATE INDEX IF NOT EXISTS `idx_certificates_hash` ON `certificates`(`file_hash`);
CREATE INDEX IF NOT EXISTS `idx_certificates_date` ON `certificates`(`date_issued`);
CREATE INDEX IF NOT EXISTS `idx_cert_authors_cert` ON `certificate_authors`(`certificate_id`);
CREATE INDEX IF NOT EXISTS `idx_cert_authors_user` ON `certificate_authors`(`user_id`);
CREATE INDEX IF NOT EXISTS `idx_audit_created` ON `audit_logs`(`created_at`);
CREATE INDEX IF NOT EXISTS `idx_audit_action` ON `audit_logs`(`action`);
CREATE INDEX IF NOT EXISTS `idx_audit_user` ON `audit_logs`(`user_id`);
CREATE INDEX IF NOT EXISTS `idx_audit_ip` ON `audit_logs`(`ip_address`);

-- Initial API Quota Seed
INSERT INTO `api_quotas` (`service_name`, `monthly_limit`, `current_usage`, `month_year`)
VALUES ('OCR_SPACE', 25000, 142, DATE_FORMAT(NOW(), '%Y-%m'))
ON DUPLICATE KEY UPDATE `last_updated` = NOW();

-- Seed Default Accounts (Bcrypt password hashes)
-- dean / admin123
-- mikhail, raejan, eljay / faculty123
INSERT INTO `users` (`id`, `username`, `password_hash`, `role`, `full_name`, `email`, `department`) VALUES
(1, 'dean', '$2y$10$bSEl36wxWz4FoKMMoM5gv.ZsnIEf89/gyF8w2Nnek3qodHgJoAiAm', 'dean', 'Dr. Arlene S. Poblador', 'dean.cici@isufst.edu.ph', 'CICI'),
(2, 'mikhail', '$2y$10$3zS6HY3Q.5WAL3lKcs9UYObgpo6Cs/T/1Nua.dtaVxLWItDCA.j2O', 'faculty', 'Prof. Levy Mikhail B. Aguilar', 'mikhail.aguilar@isufst.edu.ph', 'CICI'),
(3, 'raejan', '$2y$10$ZDvf1jW.J9c/BA/oAgbLqOSNFo1nKy0SnWex9jZsifMNEI.bghCJy', 'faculty', 'Prof. Rae Jan B. Corneja', 'raejan.corneja@isufst.edu.ph', 'CICI'),
(4, 'eljay', '$2y$10$o4NFPjy2N2jth2ATjSra9.yqvJy/s7/fSbcH6zo0TKlZwJ.GD5DKm', 'faculty', 'Prof. Eljay Muyco', 'eljay.muyco@isufst.edu.ph', 'CICI')
ON DUPLICATE KEY UPDATE `full_name` = VALUES(`full_name`);

-- Seed Faculty Profiles
INSERT INTO `faculty_profiles` (`user_id`, `employee_id`, `academic_rank`, `specialization`) VALUES
(1, 'ISUFST-6438', 'Dean, CICI', 'Educational Management & IT Leadership'),
(2, 'ISUFST-9597', 'Assistant Professor II', 'Information Security & Cloud Infrastructure'),
(3, 'ISUFST-8969', 'Instructor I', 'Database Systems & Web Engineering'),
(4, 'ISUFST-4305', 'Instructor I', 'Artificial Intelligence & Computer Vision')
ON DUPLICATE KEY UPDATE `employee_id` = VALUES(`employee_id`);

-- Seed Initial Research Publication (Co-Authored by Mikhail, Rae Jan, Eljay)
INSERT INTO `certificates` (`id`, `faculty_id`, `title`, `issuer`, `date_issued`, `category`, `file_path`, `original_filename`, `file_type`, `file_size`, `ocr_confidence`, `status`) VALUES
(1, 2, 'SECUREFACULTY: A SYSTEMATIC RECORDS MANAGEMENT PORTAL', 'Iloilo State University of Fisheries Science and Technology', '2026-09-25', 'Research Publication', 'sample_demo_cert.pdf', 'SecureFaculty_Capstone_Manuscript.pdf', 'pdf', 1048576, 0.98, 'approved')
ON DUPLICATE KEY UPDATE `title` = VALUES(`title`);

-- Seed Co-Author Mappings
INSERT INTO `certificate_authors` (`id`, `certificate_id`, `user_id`) VALUES
(1, 1, 2),
(2, 1, 3),
(3, 1, 4)
ON DUPLICATE KEY UPDATE `user_id` = VALUES(`user_id`);
