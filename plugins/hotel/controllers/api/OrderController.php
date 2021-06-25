<?php
namespace app\plugins\hotel\controllers\api;


use app\controllers\api\filters\LoginFilter;
use app\plugins\ApiController;
use app\plugins\hotel\forms\api\order\HotelOrderIntegralDirectPayForm;
use app\plugins\hotel\forms\api\order\HotelOrderPreviewForm;
use app\plugins\hotel\forms\api\order\HotelOrderSubmitForm;

class OrderController extends ApiController{

    public function behaviors(){
        return array_merge(parent::behaviors(), [
            'login' => [
                'class' => LoginFilter::class,
            ]
        ]);
    }

    /**
     * 下单预览
     * @return \yii\web\Response
     */
    public function actionPreview(){
        $form = new HotelOrderPreviewForm();
        $form->attributes = $this->requestData;
        return $this->asJson($form->preview());
    }

    /**
     * 提交订单
     * @return \yii\web\Response
     */
    public function actionSubmit(){
        $form = new HotelOrderSubmitForm();
        $form->attributes = $this->requestData;
        return $this->asJson($form->save());
    }

    /**
     * 使用红包直接支付
     * @return \yii\web\Response
     */
    public function actionIntegralDirectPay(){
        $form = new HotelOrderIntegralDirectPayForm();
        $form->attributes = $this->requestData;
        return $this->asJson($form->pay());
    }
}