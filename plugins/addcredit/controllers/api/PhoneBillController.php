<?php

namespace app\plugins\hotel\controllers\api;

use app\controllers\api\filters\LoginFilter;
use app\plugins\ApiController;
use app\plugins\hotel\forms\api\HotelDetailForm;
use app\plugins\hotel\forms\api\order\PhoneOrderPayForm;
use app\plugins\hotel\forms\api\order\PhoneOrderSubmitForm;

class PhoneBillController extends ApiController
{
    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'login' => [
                'class' => LoginFilter::class,
            ]
        ]);
    }

    /**
     * 生成订单
     * plateform_id     平台ID
     * mobile           手机号
     * order_price      充值金额
     * @return \yii\web\Response
     */
    public function actionPrepaidOrderSubmit()
    {
        $form = new PhoneOrderSubmitForm();
        $form->attributes = $this->requestData;
        return $this->asJson($form->save());
    }

    /**
     * 话费充值
     * mobile  手机号
     * price   充值金额
     * @return \yii\web\Response
     */
    public function actionPrepaidRefill()
    {
        $form = new PhoneOrderPayForm();
        $form->attributes = $this->requestData;
        return $this->asJson($form->pay());
    }

    /**
     * 酒店信息
     * @return \yii\web\Response
     */
    public function actionDetail()
    {
        $form = new HotelDetailForm();
        $form->attributes = $this->requestData;
        return $this->asJson($form->getDetail());
    }
}