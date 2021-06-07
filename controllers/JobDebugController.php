<?php
namespace app\controllers;

use app\helpers\tencent_cloud\TencentCloudCDBDescribeBinlogs;
use yii\web\Controller;

class JobDebugController extends Controller{

    public function actionIndex(){

        TencentCloudCDBDescribeBinlogs::request();
        exit;

    }

}