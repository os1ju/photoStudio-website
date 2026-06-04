<?php
require_once 'src/config/database.php';
require_once 'src/helpers/escape.php';
require_once 'src/helpers/session.php';

$photographerId = $_GET['id'] ?? 0;

// Получаем данные фотографа
$sql = "SELECT * FROM Photographers WHERE photographer_id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$photographerId]);
$photographer = $stmt->fetch();

if (!$photographer) {
    header('Location: photographers.php');
    exit;
}

// Получаем услуги фотографа
$sql = "SELECT service_id, service_name, description, price, image_url 
        FROM Services 
        WHERE photographer_id = ? AND is_active = 1";
$stmt = $pdo->prepare($sql);
$stmt->execute([$photographerId]);
$services = $stmt->fetchAll();

$currentUser = getCurrentUser();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($photographer['name']) ?> | ДомФото</title>
    <link rel="stylesheet" href="css/PHOTOGRAPH.css">
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
                <a href="logout.php">выход</a>
            <?php else: ?>
                <a href="authorization.php">вход</a>
            <?php endif; ?>
            <a href="main.php">главная</a>
        </nav>
    </header>

    <p class="title"><?= e($photographer['name']) ?></p>
    
    <div class="info">
        <img class="img" src="<?= e($photographer['avatar'] ?? 'img/anatolik.png') ?>" alt="фото <?= e($photographer['name']) ?>">
        <p class="text"><?= e($photographer['bio']) ?></p>
    </div>

    <?php if (!empty($services)): ?>
        <div class="services-section">
            <p class="services-title">Услуги фотографа</p>
            <div class="services-grid">
                <?php foreach ($services as $service): ?>
                    <div class="service-card" onclick="window.location.href='photo.php?id=<?= $service['service_id'] ?>'">
                        <img class="service-img" src="<?= e($service['image_url'] ?? 'img/card_photo.png') ?>" alt="<?= e($service['service_name']) ?>">
                        <div class="service-info">
                            <div class="service-name"><?= e($service['service_name']) ?></div>
                            <div class="service-price"><?= e(number_format($service['price'], 0, ',', ' ')) ?> руб</div>
                            <div class="service-desc"><?= e(mb_substr($service['description'], 0, 100)) ?>...</div>
                            <button class="service-btn" onclick="event.stopPropagation(); window.location.href='photo.php?id=<?= $service['service_id'] ?>'">Записаться</button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>

    <p class="small_title">Примеры фото</p>

    <div class="slider">
        <div class="slider-track">
            <div class="slide"><img src="img/photo_1.png" alt=""></div>
            <div class="slide"><img src="img/photo_2.png" alt=""></div>
            <div class="slide"><img src="img/photo_3.png" alt=""></div>
            <div class="slide"><img src="img/photo_5.png" alt=""></div>
            <div class="slide"><img src="img/photo_6.png" alt=""></div>
            <div class="slide"><img src="img/photo_7.png" alt=""></div>
        </div>
    </div>

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
    
    <script src="photo.js"></script>
</body>
</html>