<?php

namespace app\controllers\api\payCenter;


use app\controllers\api\ApiController;
use app\controllers\api\filters\LoginFilter;

class PaymentOrderPrepareController extends ApiController{

    public function behaviors(){
        return array_merge(parent::behaviors(), [
            'login' => [
                'class' => LoginFilter::class,
            ]
        ]);
    }

    /**
     * 阿里巴巴分销订单
     * @return \yii\web\Response
     */
    public function actionAlibabaDistributionOrder(){
        $form = new \app\plugins\alibaba\forms\payCenter\AlibabaDistributionOrderPrepareForm();
        $form->attributes = $this->requestData;
        return $this->asJson($form->prepare());
    }

    /**
     * 大礼包预支付单创建
     * @return \yii\web\Response
     */
    public function actionGiftpacks(){
        $form = new \app\forms\api\payCenter\paymentOrderPrepare\GiftpacksOrderPrepareForm();
        $form->attributes = $this->requestData;
        return $this->asJson($form->prepare());
    }

    /**
     * 大礼包拼单预支付单创建
     * @return \yii\web\Response
     */
    public function actionGiftpacksGroup(){
        $form = new \app\forms\api\payCenter\paymentOrderPrepare\GiftpacksGroupPrepareForm();
        $form->attributes = $this->requestData;
        return $this->asJson($form->prepare());
    }
}