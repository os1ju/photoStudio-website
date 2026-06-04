<?php

/**
 * Запуск сессии, если не запущена
 */
function startSession() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

/**
 * Установка данных пользователя в сессию
 */
function setUserSession($user) {
    startSession();
    $_SESSION['user_id'] = $user['user_id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['name'] = $user['name'];
    $_SESSION['role_id'] = $user['role_id'];
    $_SESSION['role_name'] = $user['role_name'];
}

/**
 * Получение текущего пользователя из сессии
 */
function getCurrentUser() {
    startSession();
    if (isset($_SESSION['user_id']) && isset($_SESSION['user_id'])) {
        return [
            'user_id' => $_SESSION['user_id'],
            'username' => $_SESSION['username'],
            'name' => $_SESSION['name'],
            'role_id' => $_SESSION['role_id'],
            'role_name' => $_SESSION['role_name']
        ];
    }
    return null;
}

/**
 * Проверка, авторизован ли пользователь
 */
function isLoggedIn() {
    $user = getCurrentUser();
    return $user !== null;
}

/**
 * Проверка, является ли пользователь админом
 */
function isAdmin() {
    $user = getCurrentUser();
    // Проверяем по role_id (3 - admin) или по role_name
    return $user !== null && ($user['role_id'] == 3 || $user['role_name'] === 'admin');
}

/**
 * Проверка роли пользователя
 */
function hasRole($roleName) {
    $user = getCurrentUser();
    return $user && $user['role_name'] === $roleName;
}

/**
 * Выход из системы
 */
function logout() {
    startSession();
    $_SESSION = array();
    if (isset($_COOKIE[session_name()])) {
        setcookie(session_name(), '', time() - 3600, '/');
    }
    session_destroy();
}

/**
 * Требование авторизации (редирект на страницу входа)
 */
function requireAuth() {
    if (!isLoggedIn()) {
        header('Location: ../public/authorization.php');
        exit;
    }
}

/**
 * Требование роли админа
 */
function requireAdmin() {
    requireAuth();
    if (!isAdmin()) {
        // Добавим отладочную информацию
        $user = getCurrentUser();
        error_log("RequireAdmin failed. User: " . print_r($user, true));
        header('HTTP/1.0 403 Forbidden');
        die('Доступ запрещен. Вы не являетесь администратором. Ваша роль: ' . ($user['role_name'] ?? 'не авторизован'));
    }
}