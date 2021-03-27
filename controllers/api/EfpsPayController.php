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

    /**
     * 微信支付
     * @return array
     */
    public function actionWechat(){
        $form = new EfpsPayForm();
        $form->attributes = $this->requestData;
        return $this->asJson($form->wechatPay());
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
}