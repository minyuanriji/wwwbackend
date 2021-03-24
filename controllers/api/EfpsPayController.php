<?php
namespace app\controllers\api;

use app\controllers\api\filters\LoginFilter;
use app\forms\api\order\EfpsPayForm;

class EfpsPayController extends ApiController{

    public function behaviors(){
        return array_merge(parent::behaviors(), [
            'login' => [
                'class' => LoginFilter::class,
            ]
        ]);
    }

    public function actionWechat(){

    }

    /**
     * 支付宝支付
     * @return array
     */
    public function actionAlipay(){
        $form = new EfpsPayForm();
        $form->attributes = $this->requestData;
        return $this->asJson($form->aliPay());
    }

    /**
     * 支付通知
     * @return array
     */
    public function actionNotify(){
        @file_put_contents(\Yii::getAlias("@runtime/efps_notify_data"), json_encode($_REQUEST));
    }
}