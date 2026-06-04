<?php

require_once 'src/config/database.php';
require_once 'session.php';

/**
 * Регистрация нового пользователя
 */
function registerUser($username, $name, $email, $password, $password2, $phone, $gender, $birthdate) {
    global $pdo;
    
    $errors = [];
    
    // Валидация
    if (empty($username)) {
        $errors[] = "Имя пользователя обязательно";
    } elseif (strlen($username) < 3) {
        $errors[] = "Имя пользователя должно содержать минимум 3 символа";
    }
    
    if (empty($name)) {
        $errors[] = "Ваше имя обязательно";
    }
    
    if (empty($email)) {
        $errors[] = "Email обязателен";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Некорректный email";
    }
    
    if (empty($password)) {
        $errors[] = "Пароль обязателен";
    } elseif (strlen($password) < 6) {
        $errors[] = "Пароль должен содержать минимум 6 символов";
    }
    
    if ($password !== $password2) {
        $errors[] = "Пароли не совпадают";
    }
    
    if (!empty($phone)) {
        // Очистка номера телефона от лишних символов
        $phone = preg_replace('/[^0-9+]/', '', $phone);
    }
    
    // Проверка уникальности username
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM Users WHERE username = ?");
    $stmt->execute([$username]);
    if ($stmt->fetchColumn() > 0) {
        $errors[] = "Пользователь с таким именем уже существует";
    }
    
    // Проверка уникальности email
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM Users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetchColumn() > 0) {
        $errors[] = "Пользователь с таким email уже существует";
    }
    
    // Если есть ошибки, возвращаем их
    if (!empty($errors)) {
        return ['success' => false, 'errors' => $errors];
    }
    
    // Хеширование пароля
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    
    // Роль по умолчанию - user (role_id = 2)
    $roleId = 2;
    
    // Подготовка даты рождения (может быть NULL)
    $birthdate = !empty($birthdate) ? $birthdate : null;
    
    // Вставка пользователя
    $sql = "INSERT INTO Users (username, name, phone_number, email, password, created_at, role_id) 
            VALUES (?, ?, ?, ?, ?, CURDATE(), ?)";
    $stmt = $pdo->prepare($sql);
    $result = $stmt->execute([$username, $name, $phone, $email, $hashedPassword, $roleId]);
    
    if ($result) {
        return ['success' => true, 'user_id' => $pdo->lastInsertId()];
    } else {
        return ['success' => false, 'errors' => ['Ошибка при регистрации']];
    }
}

/**
 * Авторизация пользователя
 */
function loginUser($login, $password) {
    global $pdo;
    
    $errors = [];
    
    if (empty($login)) {
        $errors[] = "Введите email или имя пользователя";
    }
    
    if (empty($password)) {
        $errors[] = "Введите пароль";
    }
    
    if (!empty($errors)) {
        return ['success' => false, 'errors' => $errors];
    }
    
    // Поиск пользователя по email или username
    $sql = "SELECT u.*, r.role_name 
            FROM Users u 
            JOIN Roles r ON u.role_id = r.role_id 
            WHERE u.email = ? OR u.username = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$login, $login]);
    $user = $stmt->fetch();
    
    if (!$user) {
        return ['success' => false, 'errors' => ['Неверный логин или пароль']];
    }
    
    // Проверка пароля
    if (!password_verify($password, $user['password'])) {
        return ['success' => false, 'errors' => ['Неверный логин или пароль']];
    }
    
    // Обновление last_login
    $updateStmt = $pdo->prepare("UPDATE Users SET last_login = NOW() WHERE user_id = ?");
    $updateStmt->execute([$user['user_id']]);
    
    // Установка сессии
    setUserSession($user);
    
    return ['success' => true, 'user' => $user];
}