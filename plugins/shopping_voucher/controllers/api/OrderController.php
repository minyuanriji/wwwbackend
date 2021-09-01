<?php

namespace app\plugins\shopping_voucher\controllers\api;


use app\plugins\ApiController;
use app\plugins\shopping_voucher\forms\api\ClosestStoreForm;

class OrderController extends ApiController{

    /**
     * 查询最近的门店
     * @return \yii\web\Response
     */
    public function actionClosestStore(){
        $form = new ClosestStoreForm();
        $form->attributes = $this->requestData;
        return $this->asJson($form->search());
    }

}