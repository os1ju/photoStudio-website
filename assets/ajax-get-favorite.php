<?php
require_once 'src/config/database.php';
require_once 'src/helpers/session.php';

header('Content-Type: application/json');

startSession();

if (!isLoggedIn()) {
    echo json_encode(['favorites' => []]);
    exit;
}

$userId = $_SESSION['user_id'];

$stmt = $pdo->prepare("SELECT service_id FROM Favorites WHERE user_id = ?");
$stmt->execute([$userId]);
$favorites = $stmt->fetchAll(PDO::FETCH_COLUMN);

echo json_encode(['favorites' => $favorites]);