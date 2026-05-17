-- Салон «Блеск» — схема MySQL 8.0+ / MariaDB 10.3+
-- Импортируйте в phpMyAdmin или: mysql -u USER -p DATABASE < database/schema.sql

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

-- Тестовые данные: настройки сайта, услуги, отзывы (всё редактируется через админку → только MySQL)
INSERT INTO site_settings (setting_key, setting_value) VALUES
('site_url', ''),
('site_name', 'Салон «Блеск»'),
('site_description', 'Маникюрный салон «Блеск» в Москве: классический маникюр, гель-лак, наращивание, педикюр, дизайн ногтей и SPA-уход. Стерильные инструменты, опытные мастера. Запись онлайн и по телефону +7 (495) 128-45-67.'),
('site_title', 'Салон «Блеск» — маникюр, гель-лак и педикюр в Москве | Онлайн-запись'),
('site_keywords', 'маникюр Москва, салон маникюра, гель-лак, педикюр, наращивание ногтей, дизайн ногтей, салон Блеск, запись на маникюр'),
('og_title', 'Салон «Блеск» — маникюр, гель-лак и педикюр в Москве'),
('og_description', 'Профессиональный маникюр и педикюр в Москве. Акции для новых клиентов, онлайн-запись на удобное время.'),
('twitter_title', 'Салон «Блеск» — маникюр и педикюр в Москве'),
('twitter_description', 'Маникюр, гель-лак, педикюр и nail-арт. Запись онлайн в салоне «Блеск».'),
('brand_short', 'Блеск'),
('brand_tagline', 'Красота в детали'),
('brand_alternate', 'Маникюрный салон «Блеск»'),
('phone_display', '+7 (495) 128-45-67'),
('phone_tel', '+74951284567'),
('email', 'info@blesk-nails.ru'),
('city', 'Москва'),
('theme_color', '#f2fae6'),
('schema_org_description', 'Профессиональный маникюрный салон в Москве. Маникюр, гель-лак, наращивание, педикюр, дизайн ногтей и SPA-уход для рук.'),
('schema_website_description', 'Маникюрный салон в Москве: маникюр, гель-лак, педикюр, дизайн ногтей и SPA-уход.'),
('og_image_alt', 'Мастер выполняет маникюр в салоне «Блеск»');

INSERT INTO services (id, name, price, price_from, image, text, detail, schema_description, sort_order) VALUES
('manicure', 'Классический маникюр', 1500, 0, 'assets/images/services/manicure.jpg',
 'Обработка кутикулы, придание формы, полировка и уходовое покрытие. Длительность — около 60 минут.',
 'Классический маникюр включает безопасную обработку кутикулы, придание формы, полировку пластины и уходовое масло.',
 'Обработка кутикулы, придание формы, полировка и уходовое покрытие.', 0),
('gel-lak', 'Гель-лак', 2200, 0, 'assets/images/services/gel-lak.jpg',
 'Стойкое покрытие, укрепление ногтевой пластины и аккуратная работа с кутикулой. Держится до 3 недель.',
 'Покрытие гель-лаком с подготовкой ногтевой пластины, базой и топом.',
 'Стойкое покрытие и укрепление ногтевой пластины.', 1),
('naraschivanie', 'Наращивание', 3500, 0, 'assets/images/services/naraschivanie.jpg',
 'Удлинение и укрепление ногтей с моделированием формы.',
 'Наращивание на типсы или формы с коррекцией архитектуры.',
 'Удлинение и укрепление ногтей с моделированием формы.', 2),
('pedicure', 'Педикюр', 2800, 0, 'assets/images/services/pedicure.jpg',
 'Аппаратная обработка стоп, уход за кожей и покрытие по желанию.',
 'Комплексный педикюр: обработка стоп и пальцев, увлажнение.',
 'Аппаратная обработка стоп и уход за кожей.', 3),
('design', 'Дизайн ногтей', 200, 1, 'assets/images/services/design.jpg',
 'Роспись, стразы, фольга и комбинированные техники.',
 'Авторский nail-арт: от минималистичного френча до сложной росписи.',
 'Роспись, стразы, фольга и комбинированные техники.', 4),
('spa', 'SPA-уход для рук', 1900, 0, 'assets/images/services/spa.jpg',
 'Пилинг, маска, массаж и увлажнение — расслабляющий уход.',
 'SPA-программа: ванночка, скраб, маска и массаж рук.',
 'Пилинг, маска, массаж и увлажнение.', 5);

INSERT INTO reviews (id, author, review_date, service, rating, text) VALUES
('r1', 'Анна К.', '2026-04-12', 'Гель-лак', 5.0,
 'Очень аккуратная работа, покрытие держится уже третью неделю без сколов.'),
('r2', 'Мария С.', '2026-04-08', 'Классический маникюр', 5.0,
 'Кутикула обработана деликатно, форма ровная. Приду снова.'),
('r3', 'Елена В.', '2026-03-28', 'Педикюр', 5.0,
 'Стерильные инструменты в индивидуальных пакетах. Стопы выглядят ухоженно.'),
('r4', 'Ольга Л.', '2026-03-20', 'Дизайн ногтей', 4.5,
 'Френч со стразами получился как на эскизе. Небольшое ожидание перед креслом.'),
('r5', 'Дарья П.', '2026-03-14', 'Наращивание', 5.0,
 'Длину и форму подобрали вместе, ногти выглядят естественно.'),
('r6', 'Ирина Н.', '2026-03-05', 'SPA-уход для рук', 5.0,
 'Расслабляющий массаж и маска — отдельное удовольствие после маникюра.');
