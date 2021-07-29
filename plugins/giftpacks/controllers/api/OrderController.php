<?php

namespace app\plugins\giftpacks\controllers\api;


use app\controllers\api\ApiController;
use app\controllers\api\filters\LoginFilter;
use app\plugins\giftpacks\forms\api\GiftpacksOrderClerkQrCodeForm;
use app\plugins\giftpacks\forms\api\GiftpacksOrderDetailForm;
use app\plugins\giftpacks\forms\api\GiftpacksOrderListForm;
use app\plugins\giftpacks\forms\api\GiftpacksOrderPackItemListForm;
use app\plugins\giftpacks\forms\api\GiftpacksOrderPreviewForm;
use app\plugins\giftpacks\forms\api\GiftpacksOrderSubmitForm;

class OrderController extends ApiController {

    public function behaviors(){
        return array_merge(parent::behaviors(), [
            'login' => [
                'class' => LoginFilter::class,
            ]
        ]);
    }

    /**
     * 订单列表
     * @return \yii\web\Response
     */
    public function actionList(){
        $form = new GiftpacksOrderListForm();
        $form->attributes = $this->requestData;
        return $this->asJson($form->getList());
    }

    /**
     * 订单详情
     * @return \yii\web\Response
     */
    public function actionDetail(){
        $form = new GiftpacksOrderDetailForm();
        $form->attributes = $this->requestData;
        return $this->asJson($form->getDetail());
    }

    /**
     * 订单礼包物品列表
     * @return \yii\web\Response
     */
    public function actionPackItemList(){
        $form = new GiftpacksOrderPackItemListForm();
        $form->attributes = $this->requestData;
        $form->city_id    = \Yii::$app->request->headers->get("x-city-id");
        $form->longitude  = ApiController::$commonData['city_data']['longitude'];
        $form->latitude   = ApiController::$commonData['city_data']['latitude'];
        return $this->asJson($form->getList());
    }


    /**
     * 预览订单
     * @return \yii\web\Response
     */
    public function actionPreview(){
        $form = new GiftpacksOrderPreviewForm();
        $form->attributes = $this->requestData;
        return $this->asJson($form->preview());
    }

    /**
     * 提交订单
     * @return \yii\web\Response
     */
    public function actionSubmit(){
        $form = new GiftpacksOrderSubmitForm();
        $form->attributes = $this->requestData;
        return $this->asJson($form->save());
    }

    /**
     * 核销码
     * @return \yii\web\Response
     */
    public function actionClerkQrCode(){
        $form = new GiftpacksOrderClerkQrCodeForm();
        $form->attributes = $this->requestData;
        return $this->asJson($form->getQrCode());
    }
}