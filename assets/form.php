<?php
require_once 'src\config\database.php';
require_once 'src\helpers\auth.php';
require_once 'src\helpers\session.php';
require_once 'src\helpers\escape.php';


// Получаем ID услуги
$serviceId = $_GET['id'] ?? 0;

// Получаем данные услуги
$sql = "SELECT s.*, c.category_name, p.name as photographer_name
        FROM Services s
        LEFT JOIN Categories c ON s.category_id = c.category_id
        LEFT JOIN Photographers p ON s.photographer_id = p.photographer_id
        WHERE s.service_id = ? AND s.is_active = 1";
$stmt = $pdo->prepare($sql);
$stmt->execute([$serviceId]);
$service = $stmt->fetch();

if (!$service) {
    header('Location: main.php');
    exit;
}

$currentUser = getCurrentUser();
$errors = [];
$success = false;

// Обработка отправки формы
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Если пользователь не авторизован, но хочет записаться как гость
    if (!$currentUser && isset($_POST['guest_mode'])) {
        $guestName = $_POST['guest_name'] ?? '';
        $guestPhone = $_POST['guest_phone'] ?? '';
        
        if (empty($guestName)) {
            $errors[] = "Введите ваше имя";
        }
        if (empty($guestPhone)) {
            $errors[] = "Введите ваш телефон";
        }
    }
    
    // Валидация даты и времени
    $bookingDate = $_POST['booking_date'] ?? '';
    $bookingTime = $_POST['booking_time'] ?? '';
    
    if (empty($bookingDate)) {
        $errors[] = "Выберите дату";
    }
    if (empty($bookingTime)) {
        $errors[] = "Выберите время";
    }
    
    // Проверка, что дата не в прошлом
    if ($bookingDate < date('Y-m-d')) {
        $errors[] = "Дата не может быть в прошлом";
    }
    
    // Если нет ошибок, сохраняем заказ
    if (empty($errors)) {
        try {
            $pdo->beginTransaction();
            
            $userId = null;
            if ($currentUser) {
                // Авторизованный пользователь
                $userId = $currentUser['user_id'];
            } else {
                // Гость: создаем временного пользователя или используем гостевой аккаунт
                // Проверяем, есть ли гость с таким телефоном
                $guestPhoneClean = preg_replace('/[^0-9]/', '', $_POST['guest_phone']);
                $stmt = $pdo->prepare("SELECT user_id FROM Users WHERE phone_number LIKE ? AND role_id = 1");
                $stmt->execute(["%$guestPhoneClean%"]);
                $guest = $stmt->fetch();
                
                if ($guest) {
                    $userId = $guest['user_id'];
                } else {
                    // Создаем нового гостя
                    $guestUsername = 'guest_' . uniqid();
                    $stmt = $pdo->prepare("INSERT INTO Users (username, name, phone_number, email, password, created_at, role_id) 
                                          VALUES (?, ?, ?, ?, ?, CURDATE(), 1)");
                    $stmt->execute([
                        $guestUsername,
                        $_POST['guest_name'],
                        $_POST['guest_phone'],
                        $guestUsername . '@temp.com',
                        password_hash(uniqid(), PASSWORD_DEFAULT),
                    ]);
                    $userId = $pdo->lastInsertId();
                }
            }
            
            // Статус "новая" = 1
            $statusId = 1;
            
            // Сохраняем заказ
            $sql = "INSERT INTO Orders (status_id, service_id, user_id, order_date, order_time, booking_date, booking_time, total_price, notes) 
                    VALUES (?, ?, ?, CURDATE(), CURTIME(), ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                $statusId,
                $serviceId,
                $userId,
                $bookingDate,
                $bookingTime,
                $service['price'],
                $_POST['notes'] ?? ''
            ]);
            
            $pdo->commit();
            $success = true;
            
            // Перенаправление через 3 секунды
            header("refresh:3;url=main.php");
            
        } catch (Exception $e) {
            $pdo->rollBack();
            $errors[] = "Ошибка при сохранении заказа. Попробуйте позже.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Запись на <?= e($service['service_name']) ?> | ДомФото</title>
    <link rel="stylesheet" href="css/form.css">

</head>
<body class="body">
    <div class="box">
        <img class="img" src="img/forms_photo.png" alt="фотка для формы">
        <div class="reg">
            <p class="title">Запись на услугу</p>
            <p class="small_title"><?= e($service['service_name']) ?></p>
            <p class="small_title" style="font-size: 14px; color: #960303;">
                Стоимость: <?= e(number_format($service['price'], 0, ',', ' ')) ?> руб
            </p>
            
            <?php if ($success): ?>
                <div class="success-message">
                    <h3> Заявка успешно отправлена!</h3>
                    <p>Мы свяжемся с вами для подтверждения записи.</p>
                    <p>Перенаправление на главную через 3 секунды...</p>
                    <a href="main.php" style="color: white; margin-top: 10px; display: inline-block;">Перейти сейчас</a>
                </div>
            <?php else: ?>
            
            <?php if (!empty($errors)): ?>
                <div class="error-message">
                    <?php foreach ($errors as $error): ?>
                        <p>• <?= e($error) ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <input type="hidden" name="service_id" value="<?= $serviceId ?>">
                
                <?php if (!$currentUser): ?>
                    <!-- Блок выбора типа пользователя для неавторизованных -->
                    <div class="auth-buttons">
                        <button type="button" class="auth-btn active" onclick="selectMode('guest')">Продолжить как гость</button>
                        <button type="button" class="auth-btn" onclick="selectMode('login')">Войти в аккаунт</button>
                    </div>
                    
                    <!-- Поля для гостя -->
                    <div id="guest-fields" class="guest-fields">
                        <div class="fields">
                            <input class="input" type="text" name="guest_name" placeholder="Введите ваше имя" value="<?= e($_POST['guest_name'] ?? '') ?>">
                            <input class="input" type="tel" name="guest_phone" placeholder="Введите ваш телефон" value="<?= e($_POST['guest_phone'] ?? '') ?>">
                        </div>
                        <p style="font-size: 12px; color: #888; margin-top: 10px;">
                            Данные нужны только для связи с вами
                        </p>
                    </div>
                    
                    <!-- Форма входа -->
                    <div id="login-fields" class="guest-fields hidden">
                        <div class="fields">
                            <input class="input" type="text" id="login_username" placeholder="Email или имя пользователя">
                            <input class="input" type="password" id="login_password" placeholder="Пароль">
                            <button type="button" class="btn" onclick="quickLogin()" style="width: 100%;">Войти</button>
                        </div>
                        <p style="font-size: 12px; color: #888; margin-top: 10px; text-align: center;">
                            <a href="registration.php" style="color: #960303;">Нет аккаунта? Зарегистрируйтесь</a>
                        </p>
                    </div>
                    
                    <input type="hidden" name="guest_mode" id="guest_mode" value="1">
                <?php else: ?>
                    <!-- Авторизованный пользователь -->
                    <div class="user-info-card">
                        <p><strong>Вы записываетесь как:</strong></p>
                        <p><?= e($currentUser['name']) ?></p>
                        <p><?= e($currentUser['username']) ?></p>
                        <p style="font-size: 12px; color: #888;">Если это не вы, <a href="logout.php" style="color: #960303;">выйдите</a> из аккаунта</p>
                    </div>
                <?php endif; ?>
                
                <div class="fields">
                    <input class="input" type="date" name="booking_date" placeholder="Выберите дату" min="<?= date('Y-m-d') ?>" value="<?= e($_POST['booking_date'] ?? '') ?>">
                    <input class="input" type="time" name="booking_time" placeholder="Выберите время" value="<?= e($_POST['booking_time'] ?? '') ?>">
                    <textarea class="input" name="notes" placeholder="Дополнительные пожелания (необязательно)" rows="3" style="resize: vertical;"><?= e($_POST['notes'] ?? '') ?></textarea>
                </div>
                
                <button type="submit" class="btn">Записаться</button>
            </form>
            
            <?php endif; ?>
            
        </div>
    </div>

    <script src="photo.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/inputmask/5.0.8/inputmask.min.js"></script>
    <script>
        // Маска для телефона
        if (document.querySelector('input[name="guest_phone"]')) {
            Inputmask("+7 999 999-99-99").mask(document.querySelector('input[name="guest_phone"]'));
        }
        
        let currentMode = 'guest';
        
        function selectMode(mode) {
            currentMode = mode;
            const guestFields = document.getElementById('guest-fields');
            const loginFields = document.getElementById('login-fields');
            const guestModeInput = document.getElementById('guest_mode');
            const btns = document.querySelectorAll('.auth-btn');
            
            btns.forEach(btn => btn.classList.remove('active'));
            
            if (mode === 'guest') {
                guestFields.classList.remove('hidden');
                loginFields.classList.add('hidden');
                btns[0].classList.add('active');
                if (guestModeInput) guestModeInput.value = '1';
            } else {
                guestFields.classList.add('hidden');
                loginFields.classList.remove('hidden');
                btns[1].classList.add('active');
                if (guestModeInput) guestModeInput.value = '0';
            }
        }
        
        function quickLogin() {
            const username = document.getElementById('login_username').value;
            const password = document.getElementById('login_password').value;
            
            if (!username || !password) {
                alert('Пожалуйста, заполните все поля');
                return;
            }
            
            fetch('ajax_login.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({username: username, password: password})
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload(); // Перезагружаем страницу, чтобы показать форму для авторизованного
                } else {
                    alert('Ошибка: ' + data.errors.join(', '));
                }
            })
            .catch(error => {
                alert('Ошибка при входе');
            });
        }
    </script>
</body>
</html>