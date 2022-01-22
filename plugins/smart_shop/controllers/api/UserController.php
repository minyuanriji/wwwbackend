<?php

namespace app\plugins\smart_shop\controllers\api;

use app\controllers\api\ApiController;
use app\plugins\smart_shop\forms\api\SmartShopUserLoginForm;

class UserController extends ApiController {

    /**
     * 用户登录
     * @return string|\yii\web\Response
     */
    public function actionLogin(){
        $form = new SmartShopUserLoginForm();
        $form->attributes = $this->requestData;
        return $this->asJson($form->login());
    }

}