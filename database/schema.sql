USE photo_studio_db;

CREATE TABLE Roles (
    role_id INT PRIMARY KEY,
    role_name VARCHAR(50) NOT NULL UNIQUE
);


CREATE TABLE Photographers (
    photographer_id SERIAL PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    bio TEXT
);


CREATE TABLE Categories (
    category_id SERIAL PRIMARY KEY,
    category_name VARCHAR(100) NOT NULL,
    description TEXT,
    photographer_id INT,
    FOREIGN KEY (photographer_id) REFERENCES Photographers(photographer_id)
);


CREATE TABLE Statuses (
    status_id INT PRIMARY KEY,
    status_name VARCHAR(50) NOT NULL UNIQUE
);


CREATE TABLE Order (
    order_id SERIAL PRIMARY KEY,
    service_id INT NOT NULL,
    status_id INT NOT NULL,
    status_name VARCHAR(50),
    FOREIGN KEY (service_id) REFERENCES Services(service_id),
    FOREIGN KEY (status_id) REFERENCES Statuses(status_id)
);


CREATE TABLE Services (
    service_id SERIAL PRIMARY KEY,
    category_id INT NOT NULL,
    service_name VARCHAR(150) NOT NULL,
    description TEXT,
    price DECIMAL(10, 2) CHECK (price >= 0),
    is_active BOOLEAN DEFAULT TRUE,
    FOREIGN KEY (category_id) REFERENCES Categories(category_id)
);


CREATE TABLE Users (
    user_id SERIAL PRIMARY KEY,
    username VARCHAR(100) NOT NULL UNIQUE,
    name VARCHAR(100),
    phone_number VARCHAR(20),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    role_id INT NOT NULL,
    FOREIGN KEY (role_id) REFERENCES Roles(role_id)
);


CREATE TABLE Favorites (
    favorite_id SERIAL PRIMARY KEY,
    user_id INT NOT NULL,
    service_id INT NOT NULL,
    FOREIGN KEY (user_id) REFERENCES Users(user_id),
    FOREIGN KEY (service_id) REFERENCES Services(service_id),
    CONSTRAINT unique_favorite UNIQUE (user_id, service_id)
);
