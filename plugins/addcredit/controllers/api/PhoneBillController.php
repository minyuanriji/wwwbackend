<?php

namespace app\plugins\addcredit\controllers\api;

use app\controllers\api\filters\LoginFilter;
use app\helpers\CityHelper;
use app\plugins\addcredit\forms\api\order\OrderForm;
use app\plugins\addcredit\forms\api\order\RechargeRecordForm;
use app\plugins\ApiController;
use app\plugins\addcredit\forms\api\order\PhoneOrderPayForm;
use app\plugins\addcredit\forms\api\order\PhoneOrderSubmitForm;

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
     * 去支付
     * order_no         订单号
     * order_price      订单金额
     * @return \yii\web\Response
     */
    public function actionPrepaidRefill()
    {
        $form = new PhoneOrderPayForm();
        $form->attributes = $this->requestData;
        return $this->asJson($form->pay());
    }

    /**
     * 充值记录
     * plateforms_id   平台ID
     * @return \yii\web\Response
     */
    public function actionRechargeRecord()
    {
        $form = new RechargeRecordForm();
        $form->attributes = $this->requestData;
        return $this->asJson($form->RechargeList());
    }

    /**
     * 查询订单状态
     * order_id   订单ID
     * @return \yii\web\Response
     */
    public function actionQueryOrderStatus()
    {
        $form = new OrderForm();
        $form->attributes = $this->requestData;
        return $this->asJson($form->OrderStatus());
    }

}