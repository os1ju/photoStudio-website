USE photo_studio_db;

CREATE TABLE Roles (
    role_id INT AUTO_INCREMENT PRIMARY KEY,
    role_name VARCHAR(50) NOT NULL UNIQUE
);
 
 
CREATE TABLE Photographers (
    photographer_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    bio TEXT
);

CREATE TABLE Users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL UNIQUE,
    name VARCHAR(100) NOT NULL,
    phone_number VARCHAR(20),
	email VARCHAR(50),
	password VARCHAR(15),
	created_at DATE,
    role_id INT NOT NULL,
	FOREIGN KEY (role_id) REFERENCES Roles(role_id)
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
    FOREIGN KEY (category_id) REFERENCES Categories(category_id)
);


CREATE TABLE Statuses (
    status_id INT AUTO_INCREMENT PRIMARY KEY,
    status_name VARCHAR(50) NOT NULL UNIQUE
);

CREATE TABLE Orders (
    order_id INT AUTO_INCREMENT PRIMARY KEY,
    status_id INT NOT NULL,
    service_id INT NOT NULL,
    status_name VARCHAR(50) NOT NULL,
	user_id INT NOT NULL,
    FOREIGN KEY (status_id) REFERENCES Statuses(status_id),
    FOREIGN KEY (service_id) REFERENCES Services(service_id),
    FOREIGN KEY (user_id) REFERENCES Users(user_id)
);


CREATE TABLE Favorites (
    favorite_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    service_id INT NOT NULL
);
