<?php


namespace app\plugins\shopping_voucher\controllers\api;

use app\controllers\api\filters\LoginFilter;
use app\plugins\ApiController;
use app\plugins\shopping_voucher\forms\api\UserShoppingVoucherListForm;

class UserController extends ApiController{

    public function behaviors(){
        return array_merge(parent::behaviors(), [
            'login' => [
                'class' => LoginFilter::class,
            ],
        ]);
    }

    /**
     * 用户购物券记录
     * @return \yii\web\Response
     */
    public function actionLog(){
        $form = new UserShoppingVoucherListForm();
        $form->attributes = $this->requestData;
        return $this->asJson($form->getList());
    }

}