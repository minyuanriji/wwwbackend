<?php

namespace app\plugins\alibaba\controllers\api;

use app\controllers\api\ApiController;
use app\controllers\api\filters\LoginFilter;
use app\plugins\alibaba\forms\api\AlibabaDistributionOrderNewDoSubmitForm;
use app\plugins\alibaba\forms\api\AlibabaDistributionOrderNewPreviewForm;

class DistributionOrderNewController extends ApiController {

    public function behaviors(){
        return array_merge(parent::behaviors(), [
            'login' => [
                'class' => LoginFilter::class,
            ],
        ]);
    }

    /**
     * 提交订单
     * @return \yii\web\Response
     */
    public function actionDoSubmit(){
        $form = new AlibabaDistributionOrderNewDoSubmitForm();
        $form->attributes = $this->requestData;
        return $this->asJson($form->submit());
    }

    /**
     * 订单预览
     * @return \yii\web\Response
     */
    public function actionPreview(){
        $form = new AlibabaDistributionOrderNewPreviewForm();
        $form->attributes = $this->requestData;
        return $this->asJson($form->preview());
    }

}