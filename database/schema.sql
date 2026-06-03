CREATE DATABASE IF NOT EXISTS domfoto_db;
USE domfoto_db;


CREATE TABLE Roles (
    role_id INT AUTO_INCREMENT PRIMARY KEY,
    role_name VARCHAR(50) NOT NULL UNIQUE
);


CREATE TABLE Users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL UNIQUE,
    name VARCHAR(100) NOT NULL,
    phone_number VARCHAR(20),
    email VARCHAR(50),
    password VARCHAR(255) NOT NULL, -- Изменено с VARCHAR(15) на VARCHAR(255) для хеша
    created_at DATE,
    role_id INT NOT NULL,
    last_login TIMESTAMP NULL,
    is_active BOOLEAN DEFAULT TRUE,
    FOREIGN KEY (role_id) REFERENCES Roles(role_id)
);


CREATE TABLE Photographers (
    photographer_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    bio TEXT,
    avatar VARCHAR(255),
    email VARCHAR(100),
    phone VARCHAR(20)
);


CREATE TABLE Categories (
    category_id INT AUTO_INCREMENT PRIMARY KEY,
    category_name VARCHAR(100) NOT NULL UNIQUE,
    description VARCHAR(100) NOT NULL
);

CREATE TABLE Services (
    service_id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT NOT NULL,
    service_name VARCHAR(100) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    photographer_id INT,
    image_url VARCHAR(255),
    FOREIGN KEY (category_id) REFERENCES Categories(category_id),
    FOREIGN KEY (photographer_id) REFERENCES Photographers(photographer_id)
);

CREATE TABLE Statuses (
    status_id INT AUTO_INCREMENT PRIMARY KEY,
    status_name VARCHAR(50) NOT NULL UNIQUE
);


CREATE TABLE Orders (
    order_id INT AUTO_INCREMENT PRIMARY KEY,
    status_id INT NOT NULL,
    service_id INT NOT NULL,
    user_id INT NOT NULL,
    order_date DATE NOT NULL,
    order_time TIME NOT NULL,
    booking_date DATE, -- дата проведения фотосессии
    booking_time TIME, -- время проведения
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    total_price DECIMAL(10,2),
    notes TEXT,
    FOREIGN KEY (status_id) REFERENCES Statuses(status_id),
    FOREIGN KEY (service_id) REFERENCES Services(service_id),
    FOREIGN KEY (user_id) REFERENCES Users(user_id)
);


CREATE TABLE Favorites (
    favorite_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    service_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES Users(user_id),
    FOREIGN KEY (service_id) REFERENCES Services(service_id),
    UNIQUE KEY unique_favorite (user_id, service_id)
);


CREATE TABLE UserSessions (
    session_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    session_token VARCHAR(255) NOT NULL UNIQUE,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expires_at TIMESTAMP NOT NULL,
    FOREIGN KEY (user_id) REFERENCES Users(user_id)
);




INSERT INTO Roles (role_name) VALUES 
('guest'),
('user'),
('admin');


INSERT INTO Statuses (status_name) VALUES 
('новая'),
('подтверждена'),
('в процессе'),
('завершена'),
('отменена');

-- Вставка тестовых пользователей (пароль: 'password123' в хешированном виде)
-- Хеш для password123: $2y$10$YourHashHere (нужно сгенерировать через password_hash)
INSERT INTO Users (username, name, phone_number, email, password, created_at, role_id) VALUES 
('john_doe', 'Иван Петров', '+7 999 123-45-67', 'ivan@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', CURDATE(), 2),
('admin', 'Администратор', '+7 999 000-00-00', 'admin@domfoto.ru', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', CURDATE(), 3);


INSERT INTO Photographers (name, bio, email, phone) VALUES 
('Анна Смирнова', 'Профессиональный фотограф с 10-летним опытом. Специализируюсь на портретной и семейной фотографии.', 'anna@domfoto.ru', '+7 999 111-22-33'),
('Михаил Козлов', 'Фотограф-документалист. Люблю снимать живые эмоции и искренние моменты.', 'mikhail@domfoto.ru', '+7 999 222-33-44'),
('Елена Васильева', 'Свадебный фотограф. Создаю нежные и романтичные фотографии.', 'elena@domfoto.ru', '+7 999 333-44-55');


INSERT INTO Categories (category_name, description) VALUES 
('Семейная фотосессия', 'Теплые семейные фотографии'),
('Портретная съемка', 'Индивидуальные портреты'),
('Свадебная съемка', 'Романтичные свадебные фото'),
('Студийная съемка', 'Профессиональная студийная съемка'),
('Уличная фотосессия', 'Съемка на природе и городских улицах');


INSERT INTO Services (category_id, service_name, description, price, is_active, photographer_id) VALUES 
(1, 'Семейный минимум', '1 час съемки, 30 обработанных фото', 5000.00, TRUE, 1),
(1, 'Семейный стандарт', '2 часа съемки, 50 обработанных фото', 8000.00, TRUE, 1),
(1, 'Семейный премиум', '3 часа съемки, 80 обработанных фото, фотокнига', 12000.00, TRUE, 1),
(2, 'Портрет в студии', '1 час в студии, 20 фото', 4000.00, TRUE, 2),
(2, 'Портрет арт', '2 часа, 40 фото, профессиональный ретушь', 7000.00, TRUE, 2),
(3, 'Свадебный день', '6 часов съемки, 200+ фото, фотоальбом', 25000.00, TRUE, 3),
(4, 'Студийный час', 'Аренда студии на 1 час', 1500.00, TRUE, NULL),
(5, 'Прогулка по городу', '1.5 часа, 40 фото', 4500.00, TRUE, 1);


INSERT INTO Orders (status_id, service_id, user_id, order_date, order_time, booking_date, booking_time, total_price) VALUES 
(4, 1, 1, CURDATE(), '14:30:00', '2026-05-15', '15:00:00', 5000.00),
(2, 5, 1, CURDATE(), '10:15:00', '2026-05-20', '11:00:00', 7000.00),
(1, 3, 1, CURDATE(), '16:00:00', '2026-05-25', '14:00:00', 12000.00);


INSERT INTO Favorites (user_id, service_id) VALUES 
(1, 2),
(1, 5),
(1, 7);
