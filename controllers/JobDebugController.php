<?php
namespace app\controllers;

use app\models\Mall;
use yii\web\Controller;

class JobDebugController extends BaseController {

    public function actionIndex(){
        $this->setWechatParmas(5);
        $wechatModel = \Yii::$app->wechat;
        if(true || $wechatModel->isWechat){
            $info = $wechatModel->app->user->get("oHQr7wg8Hf45__91vpq5VksCSK_U");
            print_r($info);
            exit;
        }

    }

}
