<?php

namespace app\plugins\giftpacks\controllers\api;

use app\controllers\api\ApiController;
use app\plugins\giftpacks\forms\api\GiftpacksDetailForm;
use app\plugins\giftpacks\forms\api\GiftpacksDetailShareForm;
use app\plugins\giftpacks\forms\api\GiftpacksGroupDetailForm;
use app\plugins\giftpacks\forms\api\GiftpacksGroupDetailShareForm;
use app\plugins\giftpacks\forms\api\GiftpacksGroupListForm;
use app\plugins\giftpacks\forms\api\GiftpacksItemDetailForm;
use app\plugins\giftpacks\forms\api\GiftpacksItemListForm;
use app\plugins\giftpacks\forms\api\GiftpacksListForm;

class GiftpacksController extends ApiController {

    /**
     * 大礼包列表
     * @return \yii\web\Response
     */
    public function actionList(){
        $form = new GiftpacksListForm();
        $form->attributes = $this->requestData;
        if (!$form->city_id && !$form->district_id) {
            if (ApiController::$commonData['city_data']['district_id']) {
                $form->district_id = ApiController::$commonData['city_data']['district_id'];
            } else {
                $form->city_id = ApiController::$commonData['city_data']['city_id'];
            }
        }
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
     * 分享大礼包
     * @return \yii\web\Response
     */
    public function actionDetailShare(){
        $form = new GiftpacksDetailShareForm();
        $form->attributes = $this->requestData;
        return $this->asJson($form->get());
    }

    /**
     * 分享拼团
     * @return \yii\web\Response
     */
    public function actionGroupDetailShare(){
        $form = new GiftpacksGroupDetailShareForm();
        $form->attributes = $this->requestData;
        return $this->asJson($form->get());
    }

    /**
     * 大礼包商品详情
     * @return \yii\web\Response
     */
    public function actionItemDetail(){
        $form = new GiftpacksItemDetailForm();
        $form->attributes = $this->requestData;
        $form->city_id    = \Yii::$app->request->headers->get("x-city-id");
        $form->longitude  = ApiController::$commonData['city_data']['longitude'];
        $form->latitude   = ApiController::$commonData['city_data']['latitude'];
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
        $form->longitude  = ApiController::$commonData['city_data']['longitude'];
        $form->latitude   = ApiController::$commonData['city_data']['latitude'];

        return $this->asJson($form->getList());
    }

    /**
     * 拼单详情
     * @return \yii\web\Response
     */
    public function actionGroupDetail(){
        $form = new GiftpacksGroupDetailForm();
        $form->attributes = $this->requestData;
        return $this->asJson($form->getDetail());
    }

    /**
     * 拼单记录
     * @return \yii\web\Response
     */
    public function actionGroupList(){
        $form = new GiftpacksGroupListForm();
        $form->attributes = $this->requestData;
        return $this->asJson($form->getList());
    }
}