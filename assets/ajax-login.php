<?php
require_once 'C:\Users\polyteh\Documents\практика воздуханства\src\config\database.php';
require_once 'C:\Users\polyteh\Documents\практика воздуханства\src\helpers\auth.php';
require_once 'C:\Users\polyteh\Documents\практика воздуханства\src\helpers\session.php';


header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'errors' => ['Метод не разрешен']]);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$username = $data['username'] ?? '';
$password = $data['password'] ?? '';

$result = loginUser($username, $password);

echo json_encode($result);