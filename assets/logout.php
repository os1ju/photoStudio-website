<?php
require_once 'src\helpers\session.php';

logout();
header('Location: authorization.php');
exit;