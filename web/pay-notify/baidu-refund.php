<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 百度退款异步通知类
 * Author: zal
 * Date: 2020-05-16
 * Time: 15:45
 */

$_GET['r'] = 'system/pay-notify/baidu-refund';

error_reporting(E_ALL);

// 注册 Composer 自动加载器
require(__DIR__ . '/../../vendor/autoload.php');

// 创建、运行一个应用
$application = new \app\core\WebApplication();
$application->run();
