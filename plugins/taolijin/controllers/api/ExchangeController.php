<?php

namespace app\plugins\taolijin\controllers\api;

use app\controllers\api\ApiController;
use app\controllers\api\filters\LoginFilter;
use app\plugins\taolijin\forms\api\ExchangeGetUrlForm;
use app\plugins\taolijin\forms\api\ExchangeIntegralToLjForm;

class ExchangeController extends ApiController {

    public function behaviors(){
        return array_merge(parent::behaviors(), [
            'login' => [
                'class' => LoginFilter::class,
            ]
        ]);
    }

    /**
     * 获取链接
     * @return \yii\web\Response
     */
    public function actionGetUrl(){
        $form = new ExchangeGetUrlForm();
        $form->attributes = $this->requestData;
        return $this->asJson($form->get());
    }

    /**
     * 使用金豆生成礼金
     * @return \yii\web\Response
     */
    public function actionIntegralToLj(){
        $form = new ExchangeIntegralToLjForm();
        $form->attributes = $this->requestData;
        return $this->asJson($form->create());
    }
}