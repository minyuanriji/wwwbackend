<?php
// comment out the following two lines when deployed to production
error_reporting(E_ALL);
define('ROOT_PATH',__DIR__);
require __DIR__ . '/../config/const.php';
$globalAutoLoader = require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../vendorEx/autoload.php';
//require __DIR__ . '/../vendor/yiisoft/yii2/Yii.php';

(new \app\core\WebApplication())->run();
//(new yii\web\Application($config))->run();
