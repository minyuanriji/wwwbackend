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
        ob_start();
        print_r($_GET);
        print($_POST);
        print_r($_REQUEST);
        echo file_get_contents("php://input");
        $content = ob_get_contents();
        ob_end_clean();
        @file_put_contents(\Yii::getAlias("@runtime/efps_notify_data"), $content);
    }

}