<?php

namespace app\plugins\oil\controllers\api;

use app\controllers\api\ApiController;
use app\controllers\api\filters\LoginFilter;
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

}