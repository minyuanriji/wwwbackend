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
        $notifyData = (array)@json_decode(@file_get_contents("php://input"), true);
        $outTradeNo = !empty($notifyData['outTradeNo']) ? $notifyData['outTradeNo'] : null;
        if(!empty($outTradeNo)){
            $job = new EfpsPayQueryJob([
                "outTradeNo" => $outTradeNo
            ]);
            $job->execute(null);
        }
    }

}