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
        $content = \Yii::$app->request->headers;
        print_r($content);
        ob_end_clean();
        file_put_contents(\Yii::getAlias("@runtime/test_efps_header"), $content);
        file_put_contents(\Yii::getAlias("@runtime/test_efps_body"), file_get_contents("php://input"));
        exit;

       /* $res = \Yii::$app->efps->payQuery([
            "customerCode" => \Yii::$app->efps->getCustomerCode(),
            "outTradeNo" => "2021032418463192039"
        ]);
        print_r($res);
        exit;*/

    }

}