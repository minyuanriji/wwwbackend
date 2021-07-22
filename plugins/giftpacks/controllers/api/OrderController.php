<?php

namespace app\plugins\giftpacks\controllers\api;


use app\controllers\api\filters\LoginFilter;
use app\plugins\ApiController;
use app\plugins\giftpacks\forms\api\GiftpacksOrderPreviewForm;

class OrderController extends ApiController{

    public function behaviors(){
        return array_merge(parent::behaviors(), [
            'login' => [
                'class' => LoginFilter::class,
            ]
        ]);
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

}