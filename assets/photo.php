<?php
require_once 'src\config\database.php';
require_once 'src\helpers\session.php';
require_once 'src\helpers\escape.php';


// Получаем ID услуги из URL
$serviceId = $_GET['id'] ?? 0;

// Получаем данные услуги
$sql = "SELECT s.*, c.category_name, p.name as photographer_name, p.phone as photographer_phone
        FROM Services s
        LEFT JOIN Categories c ON s.category_id = c.category_id
        LEFT JOIN Photographers p ON s.photographer_id = p.photographer_id
        WHERE s.service_id = ? AND s.is_active = 1";
$stmt = $pdo->prepare($sql);
$stmt->execute([$serviceId]);
$service = $stmt->fetch();

// Если услуга не найдена, перенаправляем на главную
if (!$service) {
    header('Location: main.php');
    exit;
}

$currentUser = getCurrentUser();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($service['service_name']) ?> | ДомФото</title>
    <link rel="stylesheet" href="css/photo.css">
    <style>
        /* Дополнительные стили для правильного расположения элементов */
        .info {
            display: grid;
            grid-template-columns: 1fr 1fr;
            padding: 10px;
            gap: 20px;
            align-items: start;
        }

        .info .text {
            color: white;
            padding-left: 40px;
            padding-right: 20px;
            font-size: 25px;
            font-weight: lighter;
            margin-bottom: 20px;
        }

        .btn-cost-wrapper {
            display: flex;
            align-items: center;
            gap: 30px;
            margin-left: 40px;
            margin-top: 30px;
        }

        .btn {
            margin-left: 0;
            background-color: #960303;
            width: 200px;
            height: 50px;
            border: none;
            font-size: 25px;
            font-weight: bold;
            color: white;
            transition: 0.3s ease;
            cursor: pointer;
        }

        .btn:hover {
            background-color: #6d1818;
            width: 220px;
            transition: 0.3s ease;
        }

        .cost {
            font-size: 35px;
            color: #ffffff;
            font-weight: bolder;
            margin: 0;
        }

        .photographer-info {
            color: white;
            padding-left: 40px;
            font-size: 20px;
            margin-top: 10px;
        }

        .right-col {
            display: flex;
            flex-direction: column;
        }
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
            <?php if (!$currentUser): ?>
                <a href="authorization.php">вход</a>
            <?php endif; ?>
        </nav>
    </header>

    <p class="title"><?= e($service['service_name']) ?></p>
    
    <section class="info">
        <img class="img" src="<?= e($service['image_url'] ?? 'img/card_photo.png') ?>" alt="фото услуги">
        
        <div class="right-col">
            <p class="text"><?= e($service['description']) ?></p>
            
            <?php if ($service['photographer_name']): ?>
                <p class="photographer-info"><strong>Фотограф:</strong> <?= e($service['photographer_name']) ?></p>
            <?php endif; ?>
            
            <div class="btn-cost-wrapper">
                <button class="btn" onclick="window.location.href='form.php?id=<?= $service['service_id'] ?>'">Записаться</button>
                <p class="cost"><?= e(number_format($service['price'], 0, ',', ' ')) ?> руб/час</p>
            </div>
        </div>
    </section>

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
    
    <script src="js/photo.js"></script>
</body>
</html>