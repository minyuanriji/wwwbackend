<?php

namespace app\plugins\giftpacks\controllers\api;

use app\plugins\ApiController;
use app\plugins\giftpacks\forms\api\GiftpacksDetailForm;
use app\plugins\giftpacks\forms\api\GiftpacksItemListForm;
use app\plugins\giftpacks\forms\api\GiftpacksListForm;

class GiftpacksController extends ApiController{

    /**
     * 大礼包列表
     * @return \yii\web\Response
     */
    public function actionList(){
        $form = new GiftpacksListForm();
        $form->attributes = $this->requestData;
        return $this->asJson($form->getList());
    }

    /**
     * 大礼包详情
     * @return \yii\web\Response
     */
    public function actionDetail(){
        $form = new GiftpacksDetailForm();
        $form->attributes = $this->requestData;
        return $this->asJson($form->getDetail());
    }

    /**
     * 大礼包商品记录
     * @return \yii\web\Response
     */
    public function actionItemList(){
        $form = new GiftpacksItemListForm();
        $form->attributes = $this->requestData;
        $form->city_id    = \Yii::$app->request->headers->get("x-city-id");
        $form->longitude  = \app\controllers\api\ApiController::$commonData['city_data']['longitude'];
        $form->latitude   = ApiController::$commonData['city_data']['latitude'];

        return $this->asJson($form->getList());
    }
}