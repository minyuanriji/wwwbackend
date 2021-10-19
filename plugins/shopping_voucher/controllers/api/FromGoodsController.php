<?php

namespace app\plugins\shopping_voucher\controllers\api;

use app\controllers\api\ApiController;
use app\core\ApiCode;
use app\helpers\APICacheHelper;
use app\plugins\shopping_voucher\forms\api\ShoppingVoucherFromGoodsListForm;

class FromGoodsController extends ApiController {

    public function actionList(){

        $form = new ShoppingVoucherFromGoodsListForm();
        $form->attributes   = $this->requestData;
        $form->base_mall_id = \Yii::$app->mall->id;

        $res = APICacheHelper::get($form);
        if($res['code'] == ApiCode::CODE_SUCCESS){
            $res = $res['data'];
        }

        return $this->asJson($res);
    }

}