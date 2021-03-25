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

        \Yii::$app->queue->delay(0)->push(new EfpsPayQueryJob([
            "outTradeNo" => "2021032418463192039"
        ]));

        /*ob_start();
        $content = \Yii::$app->request->headers;
        print_r($content);
        ob_end_clean();
        file_put_contents(\Yii::getAlias("@runtime/test_efps_header"), $content);
        file_put_contents(\Yii::getAlias("@runtime/test_efps_body"), file_get_contents("php://input"));
        exit;*/

       /*
        print_r($res);
        exit;*/

    }

}