<?php
require_once 'src/config/database.php';
require_once 'src/helpers/escape.php';
require_once 'src/helpers/session.php';

requireAuth();

$userId = $_SESSION['user_id'];

// Получаем избранные услуги
$sql = "SELECT s.*, c.category_name, p.name as photographer_name
        FROM Favorites f
        JOIN Services s ON f.service_id = s.service_id
        LEFT JOIN Categories c ON s.category_id = c.category_id
        LEFT JOIN Photographers p ON s.photographer_id = p.photographer_id
        WHERE f.user_id = ?
        ORDER BY f.created_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute([$userId]);
$favorites = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Избранное | ДомФото</title>
    <link rel="stylesheet" href="css/favorite.css">
    <style>

    </style>
</head>
<body class="body">
    <header>
        <div class="logo">
            <img src="img/logo.png" alt="logo">
            <span>ДомФото</span>
        </div>
        <nav>
            <a href="photographers.php">фотографы</a>
            <a href="profile.php">личный кабинет</a>
            <a href="main.php">главная</a>
        </nav>
    </header>

    <p class="title">Ваши избранные</p>
    
    <section class="cards">
        <?php if (empty($favorites)): ?>
            <div class="empty-favorites">
                <h2>У вас пока нет избранных</h2>
                <p>Добавьте понравившиеся услуги в избранное, чтобы не потерять их</p>
                <button class="btn" onclick="window.location.href='main.php'">Перейти в каталог</button>
            </div>
        <?php else: ?>
            <?php foreach ($favorites as $service): ?>
                <article class="card">
                    <div class="card-header">
                        <p class="card_t"><?= e($service['service_name']) ?> <span class="card_cost"><?= e(number_format($service['price'], 0, ',', ' ')) ?> руб</span></p>
                        <button class="remove-fav" onclick="removeFromFavorites(<?= $service['service_id'] ?>, this)">🗑️</button>
                    </div>
                    <img class="card_img" src="<?= e($service['image_url'] ?? 'img/card_photo.png') ?>" alt="тут банер">
                    <p class="card_t2">
                        <?= e($service['description']) ?>
                        <?php if (!empty($service['photographer_name'])): ?>
                            <br><small>Фотограф: <?= e($service['photographer_name']) ?></small>
                        <?php endif; ?>
                    </p>
                    <button class="btn" onclick="window.location.href='photo.php?id=<?= $service['service_id'] ?>'">Записаться</button>
                </article>  
            <?php endforeach; ?>
        <?php endif; ?>
    </section>

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

    <script>
        function removeFromFavorites(serviceId, btn) {
            fetch('ajax_favorite.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({service_id: serviceId})
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const card = btn.closest('.card');
                    card.style.opacity = '0';
                    setTimeout(() => {
                        card.remove();
                        const remainingCards = document.querySelectorAll('.card').length;
                        if (remainingCards === 0) {
                            location.reload();
                        }
                    }, 300);
                }
            });
        }
    </script>
</body>
</html>