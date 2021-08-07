<?php


namespace app\controllers\api\payCenter;


use app\controllers\api\ApiController;
use app\controllers\api\filters\LoginFilter;

class EfpsWechatPayController extends ApiController{

    public function behaviors(){
        return array_merge(parent::behaviors(), [
            'login' => [
                'class' => LoginFilter::class,
            ]
        ]);
    }


    /**
     * 支付大礼包
     * @return \yii\web\Response
     */
    public function actionGiftpacks(){
        $form = new \app\forms\api\payCenter\giftpacks\efps_wechat\WechatPayOrderForm();
        $form->attributes = $this->requestData;
        return $this->asJson($form->getJsapiParam());
    }

    /**
     * 支付大礼包拼单
     * @return \yii\web\Response
     */
    public function actionGiftpacksGroup(){
        $form = new \app\forms\api\payCenter\giftpacks\efps_wechat\WechatPayGroupForm();
        $form->attributes = $this->requestData;
        return $this->asJson($form->getJsapiParam());
    }

}