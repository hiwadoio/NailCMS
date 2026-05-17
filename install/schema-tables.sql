-- Только структура таблиц (режим «пустой сайт»)
SET NAMES utf8mb4;
SET time_zone = '+00:00';

CREATE TABLE IF NOT EXISTS site_settings (
    setting_key VARCHAR(64) NOT NULL PRIMARY KEY,
    setting_value TEXT NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS services (
    id VARCHAR(64) NOT NULL PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    price INT UNSIGNED NOT NULL DEFAULT 0,
    price_from TINYINT(1) NOT NULL DEFAULT 0,
    image VARCHAR(512) NOT NULL,
    text TEXT NOT NULL,
    detail TEXT NOT NULL,
    schema_description TEXT NOT NULL,
    sort_order INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS reviews (
    id VARCHAR(64) NOT NULL PRIMARY KEY,
    author VARCHAR(80) NOT NULL,
    review_date DATE NOT NULL,
    service VARCHAR(255) NOT NULL,
    rating DECIMAL(2,1) NOT NULL DEFAULT 5,
    text TEXT NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS pending_reviews (
    id VARCHAR(64) NOT NULL PRIMARY KEY,
    author VARCHAR(80) NOT NULL,
    review_date DATE NOT NULL,
    service VARCHAR(255) NOT NULL,
    rating DECIMAL(2,1) NOT NULL DEFAULT 5,
    text TEXT NOT NULL,
    submitted_at DATETIME NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS bookings (
    id VARCHAR(64) NOT NULL PRIMARY KEY,
    name VARCHAR(80) NOT NULL,
    phone VARCHAR(32) NOT NULL,
    email VARCHAR(255) NOT NULL DEFAULT '',
    service VARCHAR(255) NOT NULL,
    visit_date DATE NULL,
    comment TEXT NOT NULL,
    status VARCHAR(32) NOT NULL DEFAULT 'new',
    submitted_at DATETIME NOT NULL,
    updated_at DATETIME NULL,
    INDEX idx_bookings_status (status),
    INDEX idx_bookings_visit_date (visit_date),
    INDEX idx_bookings_submitted (submitted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
