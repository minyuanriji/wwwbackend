<?php

namespace app\plugins\oil\controllers;

class NotifyController extends \yii\web\Controller
{
    public function actionIndex(){
        file_put_contents(__DIR__ . "/debuglog", json_encode($_POST) . "\n", FILE_APPEND);
        die("SUCC");
    }
}