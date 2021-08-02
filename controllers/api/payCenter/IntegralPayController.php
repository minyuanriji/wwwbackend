<?php

namespace app\controllers\api\payCenter;

use app\controllers\api\ApiController;
use app\controllers\api\filters\LoginFilter;
use app\forms\api\payCenter\PayCenterIntegralPayGiftpacksGroupForm;
use app\forms\api\payCenter\PayCenterIntegralPayGiftpacksOrderForm;

class IntegralPayController extends ApiController{

    public function behaviors(){
        return array_merge(parent::behaviors(), [
            'login' => [
                'class' => LoginFilter::class,
            ]
        ]);
    }

    /**
     * 红包支付大礼包订单
     * @return \yii\web\Response
     */
    public function actionGiftpacks(){
        $form = new PayCenterIntegralPayGiftpacksOrderForm();
        $form->attributes = $this->requestData;
        return $this->asJson($form->pay());
    }

    /**
     * 红包支付大礼包拼单
     * @return \yii\web\Response
     */
    public function actionGiftpacksGroup(){
        $form = new PayCenterIntegralPayGiftpacksGroupForm();
        $form->attributes = $this->requestData;
        return $this->asJson($form->pay());
    }

}