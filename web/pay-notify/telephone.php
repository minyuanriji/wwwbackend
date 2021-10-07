<?php
/**
 * 话费异步通知类
 */

$_GET['r'] = 'api/pay-notify/wechat';

error_reporting(E_ALL);

// 注册 Composer 自动加载器
require(__DIR__ . '/../../vendor/autoload.php');

// 创建、运行一个应用
$application = new \app\core\WebApplication();
$application->run();
