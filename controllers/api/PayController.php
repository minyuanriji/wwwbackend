<?php

namespace app\controllers\api;

use app\controllers\api\filters\LoginFilter;
use app\forms\api\pay\PayGetPayDataForm;

class PayController extends ApiController{

    public function behaviors(){
        return array_merge(parent::behaviors(), [
            'login' => [
                'class' => LoginFilter::class,
            ],
        ]);
    }

    /**
     * 获取支付信息
     * @return \yii\web\Response
     */
    public function actionGetPayData(){
        $form = new PayGetPayDataForm();
        $form->attributes = $this->requestData;
        return $this->asJson($form->getData());
    }
}