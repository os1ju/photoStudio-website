<?php

$host = 'localhost';
$dbname = 'domfoto_db';
$username = 'root';    
$password = 'polyteh';           

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $username,
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
    // временное сообщение об успехе (после проверки удалить)
    echo "Подключение к БД успешно установлено!<br>";
} catch (PDOException $e) {
    die(" Ошибка подключения: " . $e->getMessage());
}
