<?php

namespace app\plugins\oil\controllers\api;

use app\controllers\api\ApiController;
use app\controllers\api\filters\LoginFilter;
use app\plugins\oil\forms\api\OilDoSubmitForm;
use app\plugins\oil\forms\api\OilPayPrepareForm;
use app\plugins\oil\forms\api\OilSubmitPreviewForm;

class OrderController extends ApiController {

    public function behaviors(){
        return array_merge(parent::behaviors(), [
            'login' => [
                'class' => LoginFilter::class,
            ]
        ]);
    }

    /**
     * 订单预览
     * @return \yii\web\Response
     */
    public function actionSubmitPreview(){
        $form = new OilSubmitPreviewForm();
        $form->attributes = $this->requestData;
        return $this->asJson($form->preview());
    }

    /**
     * 订单预览
     * @return \yii\web\Response
     */
    public function actionDoSubmit(){
        $form = new OilDoSubmitForm();
        $form->attributes = $this->requestData;
        return $this->asJson($form->doSubmit());
    }

    /**
     * 支付预处理
     * @return \yii\web\Response
     */
    public function actionPayPrepare(){
        $form = new OilPayPrepareForm();
        $form->attributes = $this->requestData;
        return $this->asJson($form->prepare());
    }
}