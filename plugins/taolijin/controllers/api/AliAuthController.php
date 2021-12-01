<?php

namespace app\plugins\taolijin\controllers\api;

use app\controllers\api\filters\LoginFilter;
use app\plugins\ApiController;
use app\plugins\taolijin\forms\api\AliAuthUrlForm;

class AliAuthController extends ApiController{

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
    public function actionGetUrl(){
        $form = new AliAuthUrlForm();
        $form->attributes = $this->requestData;
        return $this->asJson($form->get());
    }
}