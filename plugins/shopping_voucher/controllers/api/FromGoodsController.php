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

        $headers = \Yii::$app->request->headers;
        $standsMallId = isset($headers['x-stands-mall-id']) && $headers['x-stands-mall-id'] == 26 ? $headers['x-stands-mall-id'] : 0;

        $form->base_mall_id   = \Yii::$app->mall->id;
        //$form->stands_mall_id = $standsMallId;

        $res = APICacheHelper::get($form);
        if($res['code'] == ApiCode::CODE_SUCCESS){
            $res = $res['data'];
        }

        return $this->asJson($res);
    }

}