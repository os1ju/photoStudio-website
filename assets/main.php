<?php
require_once 'src\config\database.php';
require_once 'src\helpers\escape.php';
require_once 'src\config\database.php';
require_once 'src\helpers\session.php';


// Запрос к услугам
$sql = "SELECT s.service_id, s.service_name, s.description, s.price, s.image_url,
               c.category_name, p.name as photographer_name
        FROM Services s
        LEFT JOIN Categories c ON s.category_id = c.category_id
        LEFT JOIN Photographers p ON s.photographer_id = p.photographer_id
        WHERE s.is_active = 1
        ORDER BY s.service_id";
$stmt = $pdo->query($sql);
$services = $stmt->fetchAll();

$currentUser = getCurrentUser();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Главная | ДомФото</title>
    <link rel="stylesheet" href="css/main_style.css">

</head>
<body class="body">
    <header>
        <div class="logo">
            <img src="img/logo.png" alt="logo">
            <span>ДомФото</span>
        </div>
        <nav>
            <a href="photographers.php">фотографы</a>
            <?php if ($currentUser): ?>
                <a href="profile.php">личный кабинет</a>
                <a href="favorite.php">избранное</a>
                <?php if (isAdmin()): ?>
                    <a href="admin.php">админ-панель</a>
                <?php endif; ?>
                <a href="logout.php">выход (<?= e($currentUser['name']) ?>)</a>
            <?php else: ?>
                <a href="authorization.php">вход</a>
                <a href="registration.php">регистрация</a>
            <?php endif; ?>
        </nav>
    </header>
    
    <img class="banner" src="img/banner.png" alt="тут банер">

    <p class="title">Каталог работ</p>
    
    <section class="cards">
        <?php foreach ($services as $service): ?>
        <article class="card">
            <img class="card_img" src="<?= e($service['image_url'] ?? 'img/card_photo.png') ?>" alt="тут банер">
            
            <p class="card_t"><?= e($service['service_name']) ?> <span class="card_cost"><?= e(number_format($service['price'], 0, ',', ' ')) ?> руб</span></p>
            <p class="card_t2">
                <?= e($service['description']) ?>
                <?php if (!empty($service['photographer_name'])): ?>
                    <br><small>Фотограф: <?= e($service['photographer_name']) ?></small>
                <?php endif; ?>
            </p>
            <button class="btn" onclick="window.location.href='photo.php?id=<?= $service['service_id'] ?>'">Записаться</button> 
        </article>  
        <?php endforeach; ?>
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
        <?php if ($currentUser): ?>
        // Загрузка состояния избранного
        fetch('ajax_get_favorites.php')
            .then(response => response.json())
            .then(data => {
                if (data.favorites) {
                    data.favorites.forEach(serviceId => {
                        const btn = document.getElementById('fav-btn-' + serviceId);
                        if (btn) btn.style.color = '#960303';
                    });
                }
            });
        <?php endif; ?>
    </script>
</body>
</html>