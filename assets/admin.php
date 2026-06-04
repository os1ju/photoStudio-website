<?php
require_once 'src/config/database.php';
require_once 'src/helpers/session.php';
require_once 'src/helpers/escape.php';

// Требуем права администратора
requireAdmin();

// Получение статистики
$stats = [];

// Количество пользователей
$stmt = $pdo->query("SELECT COUNT(*) FROM Users");
$stats['users'] = $stmt->fetchColumn();

// Количество заказов
$stmt = $pdo->query("SELECT COUNT(*) FROM Orders");
$stats['orders'] = $stmt->fetchColumn();

// Количество услуг
$stmt = $pdo->query("SELECT COUNT(*) FROM Services WHERE is_active = 1");
$stats['services'] = $stmt->fetchColumn();

// Количество фотографов
$stmt = $pdo->query("SELECT COUNT(*) FROM Photographers");
$stats['photographers'] = $stmt->fetchColumn();

// Статистика по статусам заказов
$sql = "SELECT s.status_name, COUNT(o.order_id) as count 
        FROM Statuses s
        LEFT JOIN Orders o ON s.status_id = o.status_id
        GROUP BY s.status_id";
$stmt = $pdo->query($sql);
$statusStats = $stmt->fetchAll();

// Последние заказы
$sql = "SELECT o.*, u.username, u.name as user_name, s.service_name, st.status_name 
        FROM Orders o
        JOIN Users u ON o.user_id = u.user_id
        JOIN Services s ON o.service_id = s.service_id
        JOIN Statuses st ON o.status_id = st.status_id
        ORDER BY o.created_at DESC
        LIMIT 10";
$recentOrders = $pdo->query($sql)->fetchAll();

$currentUser = getCurrentUser();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Админ-панель | ДомФото</title>
    <link rel="stylesheet" href="css/admin.css">
</head>
<body class="body">
    <header>
        <div class="logo">
            <img src="../public/img/logo.png" alt="logo">
            <span>ДомФото | Админ-панель</span>
        </div>
        <nav>
            <a href="../public/main.php">На сайт</a>
            <a href="../public/profile.php">Профиль</a>
            <a href="../public/logout.php">Выйти</a>
        </nav>
    </header>

    <div class="admin-container">
        <div class="admin-header">
            <div class="admin-title">Добро пожаловать, <?= e($currentUser['name']) ?>!</div>
            <div class="admin-nav">
                <a href="orders.php">Заказы</a>
                <a href="services.php">Услуги</a>
                <a href="photographers.php">Фотографы</a>
                <a href="users.php">Пользователи</a>
            </div>
        </div>

 
        <div class="quick-links">
            <a href="orders.php?status=1" class="quick-link"> Новые заказы</a>
            <a href="orders.php" class="quick-link"> Все заказы</a>
            <a href="services.php" class="quick-link"> Добавить услугу</a>
            <a href="photographers.php" class="quick-link"> Управление фотографами</a>
        </div>

        <!-- Статистика -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-number"><?= $stats['users'] ?></div>
                <div class="stat-label">Пользователей</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?= $stats['orders'] ?></div>
                <div class="stat-label">Заказов</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?= $stats['services'] ?></div>
                <div class="stat-label">Активных услуг</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?= $stats['photographers'] ?></div>
                <div class="stat-label">Фотографов</div>
            </div>
        </div>

        <!-- Статусы заказов -->
        <div class="status-stats">
            <?php foreach ($statusStats as $status): ?>
                <div class="status-item">
                    <div class="status-count" style="color: <?php 
                        echo match($status['status_name']) {
                            'новая' => '#a8a8a8',
                            'подтверждена' => '#ffffff',
                            'в процессе' => '#9a2b34',
                            'завершена' => '#565656',
                            'отменена' => '#8a1f17',
                            default => '#fff'
                        };
                    ?>"><?= $status['count'] ?></div>
                    <div class="status-name"><?= $status['status_name'] ?></div>
                </div>
            <?php endforeach; ?>
        </div>


        <div class="orders-table">
            <h2>Последние заказы</h2>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Клиент</th>
                        <th>Услуга</th>
                        <th>Дата съемки</th>
                        <th>Сумма</th>
                        <th>Статус</th>
                        <th>Действия</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recentOrders as $order): ?>
                    <tr>
                        <td>#<?= $order['order_id'] ?></td>
                        <td><?= e($order['user_name']) ?></td>
                        <td><?= e($order['service_name']) ?></td>
                        <td><?= e($order['booking_date'] ?? $order['order_date']) ?></td>
                        <td><?= e(number_format($order['total_price'] ?? 0, 0, ',', ' ')) ?> руб</td>
                        <td>
                            <span class="status-badge status-<?= strtolower($order['status_name']) ?>">
                                <?= e($order['status_name']) ?>
                            </span>
                        </td>
                        <td>
                            <button class="action-btn edit-btn" onclick="editOrder(<?= $order['order_id'] ?>)">редактировать</button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <footer style="margin-top: 60px;">
        <div>
            <h2>наши контакты</h2>
            <p>+7 996 670 65 65</p>
            <p>PHOTOs@gmail.com</p>
            <p>Прокофьева ул. д 12</p>
        </div>
        <div class="vk">
            <h2>группа vk</h2>
            <img src="../public/img/vk.png" alt="QR">
        </div>
    </footer>

    <script>
        function editOrder(orderId) {
            window.location.href = 'order_edit.php?id=' + orderId;
        }
    </script>
</body>
</html>