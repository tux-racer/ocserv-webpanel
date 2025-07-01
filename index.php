<?php
require_once __DIR__ . '/controllers/OcservController.php';
$controller = new OcservController();

$login_error = '';
$result = [];
$userList = [];

if (isset($_GET['logout'])) {
    $controller->logout();
}

if (!$controller->isLoggedIn()) {
    if (isset($_POST['login'])) {
        $login_error = $controller->login($_POST);
    }
    include __DIR__ . '/views/login.php';
    exit;
}

$userList = $controller->getUserList();
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $result = $controller->handleAction($_POST);
    $userList = $controller->getUserList();
}
include __DIR__ . '/views/panel.php';
