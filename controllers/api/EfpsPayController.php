<?php
namespace app\controllers\api;

use app\controllers\api\filters\LoginFilter;
use app\forms\api\order\EfpsPayForm;
use app\forms\api\payCenter\PayCenterBalancePayForm;

class EfpsPayController extends ApiController{

    public function behaviors(){
        return array_merge(parent::behaviors(), [
            'login' => [
                'class' => LoginFilter::class,
            ]
        ]);
    }

    /**
     * 余额支付
     * @return array
     */
    public function actionBalance(){
        $form = new PayCenterBalancePayForm();
        $form->attributes = $this->requestData;
        return $this->asJson($form->doPay());
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