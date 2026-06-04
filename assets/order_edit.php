<?php
require_once 'src/config/database.php';
require_once  'src/helpers/session.php';
require_once  'src/helpers/escape.php';
requireAdmin();

$orderId = $_GET['id'] ?? 0;
$sql = "SELECT o.*, u.name as user_name, u.email, u.phone_number, s.service_name, s.price as service_price FROM Orders o
        JOIN Users u ON o.user_id = u.user_id JOIN Services s ON o.service_id = s.service_id WHERE o.order_id = ?";
$stmt = $pdo->prepare($sql); $stmt->execute([$orderId]); $order = $stmt->fetch();
if(!$order){ header('Location: orders.php'); exit; }

$statuses = $pdo->query("SELECT * FROM Statuses")->fetchAll();
$services = $pdo->query("SELECT service_id, service_name, price FROM Services WHERE is_active = 1")->fetchAll();
$success = false; $errors = [];

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $statusId = $_POST['status_id'] ?? $order['status_id'];
    $serviceId = $_POST['service_id'] ?? $order['service_id'];
    $bookingDate = $_POST['booking_date'] ?? $order['booking_date'];
    $bookingTime = $_POST['booking_time'] ?? $order['booking_time'];
    $totalPrice = $_POST['total_price'] ?? $order['total_price'];
    $notes = $_POST['notes'] ?? $order['notes'];
    try{
        $sql = "UPDATE Orders SET status_id=?, service_id=?, booking_date=?, booking_time=?, total_price=?, notes=? WHERE order_id=?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$statusId, $serviceId, $bookingDate, $bookingTime, $totalPrice, $notes, $orderId]);
        $success = true;
        $order['status_id']=$statusId; $order['service_id']=$serviceId; $order['booking_date']=$bookingDate;
        $order['booking_time']=$bookingTime; $order['total_price']=$totalPrice; $order['notes']=$notes;
    } catch(Exception $e){ $errors[]="Ошибка: ".$e->getMessage(); }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head><meta charset="UTF-8"><title>Редактирование заказа #<?= $orderId ?></title>
<link rel="stylesheet" href="css/admin.css">
<style>
    .admin-container{max-width:1000px;margin:0 auto;padding:20px}
    .edit-form{background:#2a2a2a;border-radius:10px;padding:30px;margin-top:20px}
    .form-group{margin-bottom:20px}
    .form-group label{display:block;margin-bottom:8px;color:#888;font-weight:bold}
    .form-group input,.form-group select,.form-group textarea{width:100%;padding:12px;background:#1a1a1a;border:1px solid #444;color:white;border-radius:5px}
    .btn-save{background:#4CAF50;color:white;border:none;padding:12px 30px;border-radius:5px;cursor:pointer}
    .btn-cancel{background:#666;color:white;border:none;padding:12px 30px;border-radius:5px;cursor:pointer;margin-left:10px;text-decoration:none;display:inline-block}
    .success-message{background:#4CAF50;color:white;padding:15px;border-radius:5px;margin-bottom:20px}
    .error-message{background:#f44336;color:white;padding:15px;border-radius:5px;margin-bottom:20px}
    .order-info{background:#1a1a1a;padding:15px;border-radius:5px;margin-bottom:20px}
    .back-link{display:inline-block;margin-bottom:20px;color:#960303;text-decoration:none}
    .form-row{display:grid;grid-template-columns:1fr 1fr;gap:20px}
</style>
</head>
<body class="body">
    <header><div class="logo"><img src="../public/img/logo.png" alt="logo"><span>ДомФото | Редактирование</span></div>
    <nav><a href="index.php">Главная</a><a href="orders.php">Заказы</a><a href="../public/logout.php">Выйти</a></nav></header>
    <div class="admin-container">
        <a href="orders.php" class="back-link">← Назад к заказам</a>
        <?php if($success): ?><div class="success-message"> Заказ обновлен! <a href="orders.php" style="color:white">Вернуться к списку</a></div><?php endif; ?>
        <?php if(!empty($errors)): ?><div class="error-message"><?php foreach($errors as $e): ?><p> <?= e($e) ?></p><?php endforeach; ?></div><?php endif; ?>
        <div class="order-info"><p><strong>Заказ #<?= $orderId ?></strong> | Клиент: <?= e($order['user_name']) ?> | Email: <?= e($order['email']) ?> | Тел: <?= e($order['phone_number'] ?? '—') ?></p></div>
        <form method="POST" class="edit-form">
            <div class="form-row"><div class="form-group"><label>Статус</label><select name="status_id"><?php foreach($statuses as $s): ?><option value="<?= $s['status_id'] ?>" <?= $order['status_id']==$s['status_id']?'selected':'' ?>><?= e($s['status_name']) ?></option><?php endforeach; ?></select></div>
            <div class="form-group"><label>Услуга</label><select name="service_id" id="serviceSelect"><?php foreach($services as $serv): ?><option value="<?= $serv['service_id'] ?>" data-price="<?= $serv['price'] ?>" <?= $order['service_id']==$serv['service_id']?'selected':'' ?>><?= e($serv['service_name']) ?> (<?= number_format($serv['price'],0,',',' ') ?> руб)</option><?php endforeach; ?></select></div></div>
            <div class="form-row"><div class="form-group"><label>Дата съемки</label><input type="date" name="booking_date" value="<?= e($order['booking_date']) ?>"></div>
            <div class="form-group"><label>Время</label><input type="time" name="booking_time" value="<?= e($order['booking_time']) ?>"></div></div>
            <div class="form-row"><div class="form-group"><label>Стоимость (руб)</label><input type="number" name="total_price" id="totalPrice" value="<?= e($order['total_price'] ?? $order['service_price']) ?>"></div>
            <div class="form-group"><label>&nbsp;</label><button type="button" onclick="updatePrice()" style="background:#960303;color:white;border:none;padding:12px;border-radius:5px;cursor:pointer"> Из цены услуги</button></div></div>
            <div class="form-group"><label>Примечание</label><textarea name="notes" rows="3"><?= e($order['notes']) ?></textarea></div>
            <button type="submit" class="btn-save"> Сохранить</button>
            <a href="orders.php" class="btn-cancel"> Отмена</a>
        </form>
    </div>
    <script>function updatePrice(){const select=document.getElementById('serviceSelect');const price=select.options[select.selectedIndex].getAttribute('data-price');document.getElementById('totalPrice').value=price;}</script>
</body>
</html>