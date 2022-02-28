<?php

namespace app\controllers\api\payCenter;

use app\controllers\api\ApiController;
use app\controllers\api\filters\LoginFilter;

class IntegralPayController extends ApiController{

    public function behaviors(){
        return array_merge(parent::behaviors(), [
            'login' => [
                'class' => LoginFilter::class,
            ]
        ]);
    }

    /**
     * 金豆支付大礼包订单
     * @return \yii\web\Response
     */
    public function actionGiftpacks(){
        $form = new \app\forms\api\payCenter\giftpacks\integral\PayOrderForm();
        $form->attributes = $this->requestData;
        return $this->asJson($form->pay());
    }

    /**
     * 金豆支付大礼包拼单
     * @return \yii\web\Response
     */
    public function actionGiftpacksGroup(){
        $form = new \app\forms\api\payCenter\giftpacks\integral\PayGroupForm();
        $form->attributes = $this->requestData;
        return $this->asJson($form->pay());
    }

}