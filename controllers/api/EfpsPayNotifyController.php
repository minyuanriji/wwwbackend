<?php
namespace app\controllers\api;


use app\component\jobs\EfpsPayQueryJob;
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
        \Yii::$app->queue->delay(0)->push(new EfpsPayQueryJob());
    }

}