<?php
require_once 'src/config/database.php';
require_once 'src/helpers/session.php';

header('Content-Type: application/json');

startSession();

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'require_auth' => true]);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$serviceId = $data['service_id'] ?? 0;
$userId = $_SESSION['user_id'];

if (!$serviceId) {
    echo json_encode(['success' => false, 'error' => 'Неверный ID услуги']);
    exit;
}

// Проверяем, есть ли уже в избранном
$stmt = $pdo->prepare("SELECT favorite_id FROM Favorites WHERE user_id = ? AND service_id = ?");
$stmt->execute([$userId, $serviceId]);
$exists = $stmt->fetch();

if ($exists) {
    // Удаляем из избранного
    $stmt = $pdo->prepare("DELETE FROM Favorites WHERE user_id = ? AND service_id = ?");
    $stmt->execute([$userId, $serviceId]);
    echo json_encode(['success' => true, 'action' => 'removed']);
} else {
    // Добавляем в избранное
    $stmt = $pdo->prepare("INSERT INTO Favorites (user_id, service_id) VALUES (?, ?)");
    $stmt->execute([$userId, $serviceId]);
    echo json_encode(['success' => true, 'action' => 'added']);
}