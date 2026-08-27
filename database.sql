CREATE DATABASE IF NOT EXISTS cases_shop CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE cases_shop;

CREATE TABLE IF NOT EXISTS users (
    id_user INT AUTO_INCREMENT PRIMARY KEY,
    login VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    fullname VARCHAR(100),
    role ENUM('user','admin') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS materials (
    id_material INT AUTO_INCREMENT PRIMARY KEY,
    material_name VARCHAR(100) NOT NULL
);

CREATE TABLE IF NOT EXISTS device_models (
    id_model INT AUTO_INCREMENT PRIMARY KEY,
    firm VARCHAR(100) NOT NULL,
    model_name VARCHAR(100) NOT NULL
);

CREATE TABLE IF NOT EXISTS collections (
    id_collection INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    cover_image VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS cases_catalog (
    id_case INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    count INT DEFAULT 0,
    image VARCHAR(255),
    collection_id INT,
    inscription VARCHAR(255),
    sticker TINYINT(1) DEFAULT 0,
    color VARCHAR(50),
    material_id INT,
    model_id INT,
    has_3d TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (material_id) REFERENCES materials(id_material) ON DELETE SET NULL,
    FOREIGN KEY (model_id) REFERENCES device_models(id_model) ON DELETE SET NULL,
    FOREIGN KEY (collection_id) REFERENCES collections(id_collection) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS orders (
    order_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    price DECIMAL(10,2) NOT NULL,
    status ENUM('pending','processing','completed','cancelled') DEFAULT 'pending',
    FOREIGN KEY (user_id) REFERENCES users(id_user) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS order_composition (
    compound_id INT AUTO_INCREMENT PRIMARY KEY,
    id_order INT NOT NULL,
    id_case INT NOT NULL,
    count INT NOT NULL DEFAULT 1,
    custom_design TEXT,
    FOREIGN KEY (id_order) REFERENCES orders(order_id) ON DELETE CASCADE,
    FOREIGN KEY (id_case) REFERENCES cases_catalog(id_case) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS reviews (
    id_review INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    case_id INT NOT NULL,
    rating TINYINT NOT NULL,
    text TEXT NOT NULL,
    status ENUM('pending','approved','rejected') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id_user) ON DELETE CASCADE,
    FOREIGN KEY (case_id) REFERENCES cases_catalog(id_case) ON DELETE CASCADE
);

INSERT INTO users (login, email, password, fullname, role) VALUES
('admin', 'admin@isharlotka.ru', '$2y$10$lYT4JCBzubs00V9u9duao.G4N.gZqpL8uYcEPImHL3lnoqSfDjQ4i', 'Администратор', 'admin');

INSERT INTO materials (material_name) VALUES
('Мягкий силикон'),('Жёсткий пластик'),('Натуральная кожа'),('Эко-кожа'),('Прозрачный TPU');

INSERT INTO device_models (firm, model_name) VALUES
('Apple','iPhone 14'),('Apple','iPhone 14 Pro'),('Apple','iPhone 15'),('Apple','iPhone 15 Pro'),
('Samsung','Galaxy S23'),('Samsung','Galaxy S24'),('Samsung','Galaxy A54'),
('Xiaomi','Redmi Note 12'),('Xiaomi','Mi 13 Pro');

INSERT INTO collections (name, description) VALUES
('Природа','Акварельные принты с цветами, листьями и пейзажами'),
('Космос','Звёзды, галактики и вселенная на твоём чехле'),
('Минимализм','Чистые линии и лаконичные формы'),
('Люкс','Благородные фактуры: мрамор, кожа, золото'),
('Арт','Смелые абстракции и современное искусство'),
('Бохо','Богемный стиль с этническими мотивами'),
('Город','Панорамы мегаполисов и городские огни');

INSERT INTO cases_catalog (title, description, price, count, image, collection_id, inscription, sticker, color, material_id, model_id, has_3d) VALUES
('Цветочный рай','Нежный чехол с акварельным цветочным принтом ручной работы.',890.00,15,NULL,1,NULL,1,'Розовый',1,3,0),
('Ночной минимализм','Строгий и лаконичный дизайн для тех, кто ценит стиль.',750.00,20,NULL,3,NULL,0,'Чёрный',2,4,0),
('Звёздное небо','Глубокое ночное небо с мерцающими звёздами.',1290.00,8,NULL,2,'Ad astra per aspera',1,'Тёмно-синий',1,2,1),
('Городские огни','Вдохновлён панорамой ночного города.',990.00,12,NULL,7,NULL,0,'Серый',2,5,0),
('Акварельный закат','Тёплые переходы оранжевого и алого.',850.00,18,NULL,1,NULL,1,'Оранжевый',1,6,0),
('Мраморный шик','Благородный мраморный узор с золотыми прожилками.',1450.00,7,NULL,4,NULL,0,'Белый',3,1,0),
('Бохо-цветы','Богемный стиль с полевыми цветами и листьями.',920.00,14,NULL,6,NULL,1,'Зелёный',1,7,0),
('Абстракция','Смелые геометрические формы в духе современного искусства.',1100.00,10,NULL,5,'Art is life',0,'Фиолетовый',2,8,0);

INSERT INTO reviews (user_id, case_id, rating, text, status) VALUES
(1,1,5,'Очень крутой чехол, качество отличное! Пришёл быстро, упаковка аккуратная.','approved'),
(1,3,4,'Выглядит ровно как на картинке. Силикон мягкий, не скользит. Доволен покупкой.','approved');

-- Таблица избранного
CREATE TABLE IF NOT EXISTS favorites (
    id_fav     INT AUTO_INCREMENT PRIMARY KEY,
    user_id    INT NOT NULL,
    case_id    INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uniq_fav (user_id, case_id),
    FOREIGN KEY (user_id) REFERENCES users(id_user) ON DELETE CASCADE,
    FOREIGN KEY (case_id) REFERENCES cases_catalog(id_case) ON DELETE CASCADE
);

-- Таблица сохранённых дизайнов (шаблонов из конструктора)
CREATE TABLE IF NOT EXISTS saved_designs (
    id_design   INT AUTO_INCREMENT PRIMARY KEY,
    user_id     INT NOT NULL,
    case_id     INT NOT NULL,
    title       VARCHAR(150) DEFAULT 'Мой дизайн',
    design_data LONGTEXT NOT NULL,
    share_token VARCHAR(40) UNIQUE NOT NULL,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id_user) ON DELETE CASCADE,
    FOREIGN KEY (case_id) REFERENCES cases_catalog(id_case) ON DELETE CASCADE
);

-- ── Миграция для уже существующей базы данных ──────────────────────────────
-- Если таблица cases_catalog уже создана БЕЗ поля has_3d (старая версия БД),
-- выполните эту строку отдельно в phpMyAdmin (вкладка SQL):
--
-- ALTER TABLE cases_catalog ADD COLUMN has_3d TINYINT(1) DEFAULT 0;
-- UPDATE cases_catalog SET has_3d = 1 WHERE title = 'Звёздное небо';
