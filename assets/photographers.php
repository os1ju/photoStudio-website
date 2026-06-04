<?php
require_once 'src/config/database.php';
require_once 'src/helpers/escape.php';
require_once 'src/helpers/session.php';

// Получаем всех фотографов
$sql = "SELECT photographer_id, name, bio, avatar, email, phone 
        FROM Photographers 
        ORDER BY photographer_id";
$stmt = $pdo->query($sql);
$photographers = $stmt->fetchAll();

$currentUser = getCurrentUser();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Наши фотографы | ДомФото</title>
    <link rel="stylesheet" href="css/photographers.css">
</head>
<body class="body">
    <header>
        <div class="logo">
            <img src="img/logo.png" alt="logo">
            <span>ДомФото</span>
        </div>
        <nav>
            <a href="main.php">главная</a>
            <?php if ($currentUser): ?>
                <a href="profile.php">личный кабинет</a>
                <a href="favorite.php">избранное</a>
                <?php if (isAdmin()): ?>
                    <a href="../admin/index.php">админ-панель</a>
                <?php endif; ?>
                <a href="logout.php">выход</a>
            <?php else: ?>
                <a href="authorization.php">вход</a>
                <a href="registration.php">регистрация</a>
            <?php endif; ?>
        </nav>
    </header>

    <p class="title">Наши фотографы</p>
    
    <?php foreach ($photographers as $photographer): ?>
    <section class="cards">
        <article class="card" onclick="window.location.href='photographer.php?id=<?= $photographer['photographer_id'] ?>'">
            <img class="card_img" src="<?= e($photographer['avatar'] ?? 'img/anatolik.png') ?>" alt="<?= e($photographer['name']) ?>">
            <div class="card_text">
                <p class="card_title"><?= e($photographer['name']) ?></p>
                <p><?= e($photographer['bio']) ?></p>
            </div>
        </article>  
    </section>
    <?php endforeach; ?>

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