<?php

namespace app\plugins\giftpacks\controllers\api;

use app\plugins\ApiController;
use app\plugins\giftpacks\forms\api\HotelGiftpacksListForm;

class GiftpacksController extends ApiController{

    /**
     * 大礼包列表
     * @return \yii\web\Response
     */
    public function actionList(){
        $form = new HotelGiftpacksListForm();
        $form->attributes = $this->requestData;
        return $this->asJson($form->getList());
    }

}