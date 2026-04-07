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

CREATE TABLE IF NOT EXISTS cases_catalog (
    id_case INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    count INT DEFAULT 0,
    image VARCHAR(255),
    collection VARCHAR(100),
    inscription VARCHAR(255),
    sticker TINYINT(1) DEFAULT 0,
    color VARCHAR(50),
    material_id INT,
    model_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (material_id) REFERENCES materials(id_material) ON DELETE SET NULL,
    FOREIGN KEY (model_id) REFERENCES device_models(id_model) ON DELETE SET NULL
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

-- Admin: login=admin, password=admin123
INSERT INTO users (login, email, password, fullname, role) VALUES
('admin', 'admin@isharlotka.ru', '$2y$10$TKh8H1.PfunDqTQRkv.PKuAIsGy5UQSIQP7YJVHnLR8FPxCcWzGO2', 'Администратор', 'admin');

INSERT INTO materials (material_name) VALUES
('Мягкий силикон'),('Жёсткий пластик'),('Натуральная кожа'),('Эко-кожа'),('Прозрачный TPU');

INSERT INTO device_models (firm, model_name) VALUES
('Apple','iPhone 14'),('Apple','iPhone 14 Pro'),('Apple','iPhone 15'),('Apple','iPhone 15 Pro'),
('Samsung','Galaxy S23'),('Samsung','Galaxy S24'),('Samsung','Galaxy A54'),
('Xiaomi','Redmi Note 12'),('Xiaomi','Mi 13 Pro');

INSERT INTO cases_catalog (title, description, price, count, image, collection, inscription, sticker, color, material_id, model_id) VALUES
('Цветочный рай','Нежный чехол с акварельным цветочным принтом ручной работы. Каждый лепесток прорисован вручную.',890.00,15,NULL,'Природа',NULL,1,'Розовый',1,3),
('Ночной минимализм','Строгий и лаконичный дизайн для тех, кто ценит стиль без лишних деталей.',750.00,20,NULL,'Минимализм',NULL,0,'Чёрный',2,4),
('Звёздное небо','Глубокое ночное небо с мерцающими звёздами. Светится в темноте.',1290.00,8,NULL,'Космос','Ad astra per aspera',1,'Тёмно-синий',1,2),
('Городские огни','Вдохновлён панорамой ночного города с бликами огней.',990.00,12,NULL,'Город',NULL,0,'Серый',2,5),
('Акварельный закат','Тёплые переходы оранжевого и алого. Как последний луч солнца.',850.00,18,NULL,'Природа',NULL,1,'Оранжевый',1,6),
('Мраморный шик','Благородный мраморный узор с золотыми прожилками.',1450.00,7,NULL,'Люкс',NULL,0,'Белый',3,1),
('Бохо-цветы','Богемный стиль с полевыми цветами и листьями.',920.00,14,NULL,'Бохо',NULL,1,'Зелёный',1,7),
('Абстракция','Смелые геометрические формы в духе современного искусства.',1100.00,10,NULL,'Арт','Art is life',0,'Фиолетовый',2,8);
