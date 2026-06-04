<?php
require_once 'src/config/database.php';
require_once 'src/helpers/auth.php';
require_once 'src/helpers/session.php';
require_once 'src/helpers/escape.php';

// Если уже авторизован, перенаправляем
if (isLoggedIn()) {
    $user = getCurrentUser();
    if (isAdmin()) {
        header('Location: ../admin/index.php');
    } else {
        header('Location: profile.php');
    }
    exit;
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login = $_POST['login'] ?? '';
    $password = $_POST['password'] ?? '';
    
    $result = loginUser($login, $password);
    
    if ($result['success']) {
        $user = getCurrentUser();
        if (isAdmin()) {
            header('Location: ../admin/index.php');
        } else {
            header('Location: profile.php');
        }
        exit;
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
    <title>Вход | ДомФото</title>
    <link rel="stylesheet" href="css/authorization.css">
</head>
<body class="body">
    <div class="box">
        <img class="img" src="img/forms_photo.png" alt="фотка для формы">
        <div class="reg">
            <p class="title">Вход</p>
            
            <?php if (!empty($errors)): ?>
                <div id="error-messages" style="color: red; margin-top: 10px; background: rgba(255,0,0,0.1); padding: 10px; border-radius: 5px;">
                    <?php foreach ($errors as $error): ?>
                        <p style="margin: 5px 0;">• <?= e($error) ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="fields">
                    <input id="login-email" name="login" class="input" type="text" placeholder="Email или имя пользователя" value="<?= e($_POST['login'] ?? '') ?>" required>
                    <input id="login-password" name="password" class="input" type="password" placeholder="Пароль" required>
                </div>
                <a class="link" href="registration.php">ещё нет аккаунта?</a>
                <button type="submit" id="btn-login" class="btn">Войти</button>
            </form>
        </div>
    </div>
</body>
</html>