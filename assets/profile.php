
<?php
require_once 'src\config\database.php';
require_once 'src\helpers\session.php';
require_once 'src\helpers\escape.php';
requireAuth();

$user = getCurrentUser();

// Получаем последние 3 заказа
$sql = "SELECT o.*, s.service_name, s.price, st.status_name 
        FROM Orders o
        JOIN Services s ON o.service_id = s.service_id
        JOIN Statuses st ON o.status_id = st.status_id
        WHERE o.user_id = ?
        ORDER BY o.created_at DESC
        LIMIT 3";
$stmt = $pdo->prepare($sql);
$stmt->execute([$user['user_id']]);
$recentOrders = $stmt->fetchAll();

// Получаем избранное (первые 3)
$sql = "SELECT f.*, s.service_name, s.price, s.image_url 
        FROM Favorites f
        JOIN Services s ON f.service_id = s.service_id
        WHERE f.user_id = ?
        ORDER BY f.created_at DESC
        LIMIT 3";
$stmt = $pdo->prepare($sql);
$stmt->execute([$user['user_id']]);
$recentFavorites = $stmt->fetchAll();

$totalOrders = 0;
$stmt = $pdo->prepare("SELECT COUNT(*) FROM Orders WHERE user_id = ?");
$stmt->execute([$user['user_id']]);
$totalOrders = $stmt->fetchColumn();

$totalFavorites = 0;
$stmt = $pdo->prepare("SELECT COUNT(*) FROM Favorites WHERE user_id = ?");
$stmt->execute([$user['user_id']]);
$totalFavorites = $stmt->fetchColumn();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Личный кабинет | ДомФото</title>
    <link rel="stylesheet" href="css/profile.css">
    <style>
        .section-header a {
            color: #960303;
            text-decoration: none;
            font-size: 14px;
        }
        .section-header a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

<header>
    <div class="logo">
        <img src="img/logo.png" alt="logo">
        <span>ДомФото</span>
    </div>
    <nav>
        <a href="photographers.php">фотографы</a>
        <a href="main.php">главная</a>
        <a href="favorite.php">избранное</a>
    </nav>
</header>

<main class="cabinet">
    <aside class="profile">
        <div class="user-info">
            <img class="avatar" src="img/photo_2.png" alt="фото профиля">
            <div>
                <h3><?= e($user['name']) ?></h3>
                <p><?= e($user['username']) ?></p>
                <a href="#">редактировать</a>
            </div>
        </div>
        <div class="menu">
            <a href="orders.php">Мои заказы (<?= $totalOrders ?>)</a>
            <a href="main.php">Записаться</a>
            <a href="favorite.php">Избранное (<?= $totalFavorites ?>)</a>
            <div class="logout">
                <a href="logout.php">Выйти →</a>
            </div>
        </div>
    </aside>

    <section class="content">
        <div class="orders">
            <div class="section-header">
                <h2>Мои заказы</h2>
                <?php if ($totalOrders > 0): ?>
                    <a href="orders.php">все заказы (<?= $totalOrders ?>) →</a>
                <?php endif; ?>
            </div>

            <?php if (empty($recentOrders)): ?>
                <p style="text-align: center; padding: 40px;">У вас пока нет заказов</p>
            <?php else: ?>
                <?php foreach ($recentOrders as $order): ?>
                <div class="order-card">
                    <img src="img/order.png" alt="order">
                    <div class="order-info">
                        <h3><?= e($order['service_name']) ?></h3>
                        <p>Дата: <?= e($order['booking_date'] ?? $order['order_date']) ?> <?= e($order['booking_time'] ?? '') ?></p>
                        <p>Фотограф: <?= e($order['photographer_name'] ?? 'не указан') ?></p>
                        <p>Статус: <strong><?= e($order['status_name']) ?></strong></p>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <div class="bottom-grid">
            <div class="booking">
                <h2>Запись</h2>
                <div class="booking-card">
                    <p>Выберите дату и время</p>
                    <button class="btn" onclick="window.location.href='main.php'">Записаться</button>
                </div>
            </div>

            <div class="favorites">
                <div class="section-header">
                    <h2>Избранные</h2>
                    <?php if ($totalFavorites > 0): ?>
                        <a href="favorite.php">смотреть все (<?= $totalFavorites ?>) →</a>
                    <?php endif; ?>
                </div>
                <div class="gallery">
                    <?php if (empty($recentFavorites)): ?>
                        <p style="color: #888; grid-column: 1/-1; text-align: center;">Нет избранных</p>
                    <?php else: ?>
                        <?php foreach ($recentFavorites as $fav): ?>
                            <img src="<?= e($fav['image_url'] ?? 'img/card_photo.png') ?>" alt="<?= e($fav['service_name']) ?>">
                        <?php endforeach; ?>
                        <?php if ($totalFavorites > 3): ?>
                            <div class="more">+<?= $totalFavorites - 3 ?></div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>
</main>

<footer>
    <div>
        <h2>наши контакты</h2>
        <p>+7 996 670 65 65</p>
        <p>PHOTOs@gmail.com</p>
        <p>Прокофьева ул. д 12</p>
    </div>
    <div class="vk">
        <h2>группа vk</h2>
        <img src="img/vk.png" alt="QR">
    </div>
</footer>

</body>
</html>