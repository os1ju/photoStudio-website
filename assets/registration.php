<?php
require_once 'src\config\database.php';
require_once 'src\helpers\auth.php';
require_once 'src\helpers\session.php';
require_once 'src\helpers\escape.php';

// Если уже авторизован, перенаправляем в личный кабинет
if (isLoggedIn()) {
    header('Location: profile.php');
    exit;
}

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $password2 = $_POST['password2'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $gender = $_POST['gender'] ?? '';
    $birthdate = $_POST['birthdate'] ?? '';
    
    $result = registerUser($username, $name, $email, $password, $password2, $phone, $gender, $birthdate);
    
    if ($result['success']) {
        $success = true;
        // Автоматический вход после регистрации
        $loginResult = loginUser($username, $password);
        if ($loginResult['success']) {
            header('Location: profile.php');
            exit;
        }
    } else {
        $errors = $result['errors'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Регистрация | ДомФото</title>
    <link rel="stylesheet" href="css/registration.css">
</head>
<body class="body">
    <div class="box">
        <img class="img" src="img/forms_photo.png" alt="фотка для формы">
        <div class="reg">
            <p class="title">Регистрация</p>
            
            <?php if (!empty($errors)): ?>
                <div class="error-messages" style="color: red; margin-top: 10px; background: rgba(255,0,0,0.1); padding: 10px; border-radius: 5px;">
                    <?php foreach ($errors as $error): ?>
                        <p style="margin: 5px 0;">• <?= e($error) ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="success-messages" style="color: green; margin-top: 10px;">
                    <p>Регистрация успешна! Перенаправление...</p>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="fields">
                    <input id="reg-username" name="username" class="input" type="text" placeholder="Имя пользователя" value="<?= e($_POST['username'] ?? '') ?>" required>
                    <input id="reg-name" name="name" class="input" type="text" placeholder="Ваше имя" value="<?= e($_POST['name'] ?? '') ?>" required>
                    <input id="reg-email" name="email" class="input" type="email" placeholder="Почта" value="<?= e($_POST['email'] ?? '') ?>" required>
                    <input id="reg-password" name="password" class="input" type="password" placeholder="Пароль" required>
                    <input id="reg-password2" name="password2" class="input" type="password" placeholder="Повторите пароль" required>
                    <input id="reg-phone" name="phone" class="input" type="tel" placeholder="Номер телефона" value="<?= e($_POST['phone'] ?? '') ?>">
                    <div class="gender">
                        <p class="text">Пол</p>
                        <label>
                            <input type="radio" name="gender" value="male" <?= ($_POST['gender'] ?? '') === 'male' ? 'checked' : '' ?>> м
                        </label>
                        <label>
                            <input type="radio" name="gender" value="female" <?= ($_POST['gender'] ?? '') === 'female' ? 'checked' : '' ?>> ж
                        </label>
                    </div>
                    <input id="reg-birthdate" name="birthdate" class="input" type="date" placeholder="дата рождения" value="<?= e($_POST['birthdate'] ?? '') ?>">
                </div>
                <a class="link" href="authorization.php">уже есть аккаунт?</a>
                <button type="submit" id="btn-register" class="btn">Зарегистрироваться</button>
            </form>
        </div>
    </div>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/inputmask/5.0.8/inputmask.min.js"></script>
    <script>
        Inputmask("+7 999 999-99-99").mask(document.getElementById("reg-phone"));
    </script>
</body>
</html>