<?php
namespace app\controllers;

use app\models\Mall;
use yii\web\Controller;

class JobDebugController extends BaseController {

    public function actionIndex(){
        $this->setWechatParmas(5);
        $wechatModel = \Yii::$app->wechat;
        if($wechatModel->isWechat){
            $info = $wechatModel->app->user->get("oHQr7wg8Hf45__91vpq5VksCSK_U");
            if(isset($info['subscribe']) && $info['subscribe'] == 1){ //已关注

            }else{ //未关注

            }
            print_r($info);
            exit;
        }

    }

}