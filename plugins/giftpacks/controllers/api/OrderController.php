<?php

namespace app\plugins\giftpacks\controllers\api;


use app\controllers\api\ApiController;
use app\controllers\api\filters\LoginFilter;
use app\plugins\giftpacks\forms\api\GiftpacksOrderDetailForm;
use app\plugins\giftpacks\forms\api\GiftpacksOrderListForm;
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
}