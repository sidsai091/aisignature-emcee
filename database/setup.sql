-- =============================================
-- Ameerul Iskandar Emcee Booking System
-- Database Setup Script
-- =============================================

CREATE DATABASE IF NOT EXISTS `emcee_booking` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `emcee_booking`;

-- Bookings table
CREATE TABLE IF NOT EXISTS `bookings` (
    `id`          INT AUTO_INCREMENT PRIMARY KEY,
    `name`        VARCHAR(255) NOT NULL,
    `email`       VARCHAR(255) NOT NULL,
    `phone`       VARCHAR(50)  NOT NULL,
    `company`     VARCHAR(255) DEFAULT '',
    `event_type`  VARCHAR(100) NOT NULL,
    `date`        DATE         NOT NULL,
    `time`        VARCHAR(20)  NOT NULL,
    `venue`       VARCHAR(255) NOT NULL,
    `duration`    VARCHAR(50)  NOT NULL,
    `package`     VARCHAR(50)  NOT NULL,
    `addons`      VARCHAR(255) DEFAULT '',
    `notes`       TEXT         DEFAULT NULL,
    `admin_notes` TEXT         DEFAULT NULL,
    `status`      ENUM('Pending','Confirmed','Completed','Cancelled') DEFAULT 'Pending',
    `created_at`  DATETIME     DEFAULT CURRENT_TIMESTAMP,
    `updated_at`  DATETIME     DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert sample data (matching existing mock data)
INSERT INTO `bookings` (`name`, `email`, `phone`, `company`, `event_type`, `date`, `time`, `venue`, `duration`, `package`, `addons`, `notes`, `admin_notes`, `status`, `created_at`) VALUES
('Sarah Mitchell',    'sarah@apexfg.com',       '+60 12-111 2222', 'Apex Financial Group',   'Corporate Event', '2026-04-18', '19:00', 'Grand Hyatt KL',          '4hrs',    'standard', '', '', 'VIP table arrangement needed.',       'Confirmed',  '2026-03-20 10:00:00'),
('James & Priya Tan', 'james.tan@gmail.com',    '+60 11-333 4444', '',                        'Wedding',         '2026-05-03', '16:00', 'The Majestic Hotel',      'fullday', 'premium',  '', '', 'Bilingual hosting required (EN/CN).', 'Confirmed',  '2026-03-22 10:00:00'),
('Rachel Okonkwo',    'rachel@novatech.io',     '+60 17-555 6666', 'NovaTech Industries',     'Product Launch',  '2026-04-25', '14:00', 'Setia City Convention',   '4hrs',    'standard', '', '', '',                                    'Pending',    '2026-03-28 10:00:00'),
('David Lim',         'david.lim@hrcorp.com',   '+60 16-777 8888', 'HR Corp Malaysia',        'Gala Dinner',     '2026-04-30', '19:30', 'Mandarin Oriental KL',   '4hrs',    'standard', '', '', '400 pax, black tie event.',           'Pending',    '2026-04-01 10:00:00'),
('Nurul Ain',         'nurul@klfoundation.org', '+60 19-999 0000', 'KL Community Foundation', 'Charity Event',   '2026-05-15', '18:00', 'Perdana Botanical Garden','4hrs',    'basic',    '', '', 'Outdoor event, weather contingency.', 'Pending',    '2026-04-02 10:00:00'),
('Tan Sri Ahmad',     'ahmad@conglomco.my',     '+60 12-100 2000', 'Conglom Holdings',        'Awards Ceremony', '2026-03-14', '20:00', 'Sunway Pyramid Convention','4hrs',   'premium',  '', '', 'Went smoothly. Client very happy.',   'Completed',  '2026-02-10 10:00:00'),
('Lena Hoffmann',     'lena@euromed.de',        '+49 176 1234 5678','EuroMed GmbH',           'Conference',      '2026-03-22', '09:00', 'KLCC Convention Centre', 'fullday', 'premium',  '', '', 'International summit, 800 delegates.','Completed',  '2026-02-15 10:00:00'),
('Farah Zulkifli',    'farah@glam.com.my',      '+60 13-200 3000', 'Glam Events Sdn Bhd',     'Corporate Event', '2026-06-12', '19:00', 'Four Seasons KL',        '4hrs',    'standard', '', '', 'Annual dinner for 300 staff.',        'Pending',    '2026-04-05 10:00:00'),
('Marcus Wong',       'marcus@startupx.io',     '+60 14-400 5000', 'StartupX Asia',           'Product Launch',  '2026-06-20', '15:00', '1 Utama Shopping Centre','2hrs',    'basic',    '', '', 'Client cancelled — venue issue.',     'Cancelled',  '2026-04-06 10:00:00'),
('Siti Norzahra',     'siti@weddinglux.my',     '+60 18-600 7000', '',                        'Wedding',         '2026-07-05', '16:30', 'Shangri-La Hotel KL',    'fullday', 'premium',  '', '', 'Garden ceremony + ballroom dinner.',  'Pending',    '2026-04-08 10:00:00');
