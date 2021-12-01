<?php

namespace app\plugins\taolijin\controllers\api;

use app\controllers\api\filters\LoginFilter;
use app\plugins\ApiController;
use app\plugins\taolijin\forms\api\AuthGetInfoForm;

class AuthController extends ApiController{

    public function behaviors(){
        return array_merge(parent::behaviors(), [
            'login' => [
                'class' => LoginFilter::class,
            ]
        ]);
    }

    /**
     * 获取授权链接
     * @return \yii\web\Response
     */
    public function actionGetInfo(){
        $form = new AuthGetInfoForm();
        $form->attributes = $this->requestData;
        return $this->asJson($form->getInfo());
    }
}