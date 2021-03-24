<?php
namespace app\controllers\api;


use yii\web\Controller;

class EfpsPayNotifyController extends Controller{

    public function init(){
        parent::init();
        $this->enableCsrfValidation = false;
    }


    /**
     * 支付通知
     * @return array
     */
    public function actionAliJsApiPayment(){
        @file_put_contents(\Yii::getAlias("@runtime/efps_notify_data"), json_encode($_REQUEST));
    }

}