CREATE TABLE roles (
    role_id INT PRIMARY KEY,
    role_name VARCHAR(50) NOT NULL UNIQUE
);


CREATE TABLE users (
    user_id SERIAL PRIMARY KEY,
    username VARCHAR(100) NOT NULL UNIQUE,
    name VARCHAR(100),
    phone_number VARCHAR(20),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    role_id INT NOT NULL,
    FOREIGN KEY (role_id) REFERENCES roles(role_id)
);


CREATE TABLE photographers (
    photographer_id SERIAL PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    bio TEXT
);


CREATE TABLE categories (
    category_id SERIAL PRIMARY KEY,
    category_name VARCHAR(100) NOT NULL,
    description TEXT,
    photographer_id INT,
    FOREIGN KEY (photographer_id) REFERENCES photographers(photographer_id)
);


CREATE TABLE services (
    service_id SERIAL PRIMARY KEY,
    category_id INT NOT NULL,
    service_name VARCHAR(150) NOT NULL,
    description TEXT,
    price DECIMAL(10, 2) CHECK (price >= 0),
    is_active BOOLEAN DEFAULT TRUE,
    FOREIGN KEY (category_id) REFERENCES categories(category_id)
);


CREATE TABLE statuses (
    status_id INT PRIMARY KEY,
    status_name VARCHAR(50) NOT NULL UNIQUE
);


CREATE TABLE bookings (
    booking_id SERIAL PRIMARY KEY,
    user_id INT NOT NULL,
    service_id INT NOT NULL,
    booking_date DATE NOT NULL,
    booking_time TIME NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status_id INT NOT NULL DEFAULT 1, -- По умолчанию "Новая"
    FOREIGN KEY (user_id) REFERENCES users(user_id),
    FOREIGN KEY (service_id) REFERENCES services(service_id),
    FOREIGN KEY (status_id) REFERENCES statuses(status_id)
);


CREATE TABLE favorites (
    favorite_id SERIAL PRIMARY KEY,
    user_id INT NOT NULL,
    service_id INT NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(user_id),
    FOREIGN KEY (service_id) REFERENCES services(service_id),
    UNIQUE (user_id, service_id) -- Запрет дублирования избранных услуг
);


CREATE TABLE orders (
    order_id SERIAL PRIMARY KEY,
    status_id INT NOT NULL,
    service_id INT NOT NULL,
    status_name VARCHAR(50), -- Денормализованное поле для быстрого отображения статуса
    FOREIGN KEY (status_id) REFERENCES statuses(status_id),
    FOREIGN KEY (service_id) REFERENCES services(service_id)
);
