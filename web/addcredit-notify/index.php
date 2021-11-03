<?php

$_GET['r'] = 'plugin/addcredit/api/notify/do-notify&order_id=' . $_POST['orderId'];

error_reporting(E_ALL);

// 注册 Composer 自动加载器
require(__DIR__ . '/../../vendor/autoload.php');

// 创建、运行一个应用
$application = new \app\core\WebApplication();
$application->run();

