<?php

namespace app\plugins\alibaba\controllers\api;

use app\controllers\api\ApiController;
use app\controllers\api\filters\LoginFilter;
use app\plugins\alibaba\forms\api\AlibabaDistributionOrderRefundApplyForm;
use app\plugins\alibaba\forms\api\AlibabaDistributionOrderRefundReasonsForm;

class DistributionOrderRefundController extends ApiController {

    public function behaviors(){
        return array_merge(parent::behaviors(), [
            'login' => [
                'class' => LoginFilter::class,
            ],
        ]);
    }

    /**
     * 退款申请
     * @return \yii\web\Response
     */
    public function actionApply(){
        $form = new AlibabaDistributionOrderRefundApplyForm();
        $form->attributes = $this->requestData;
        return $this->asJson($form->doApply());
    }

    /**
     * 退款原因列表
     * @return \yii\web\Response
     */
    public function actionReasons(){
        $form = new AlibabaDistributionOrderRefundReasonsForm();
        $form->attributes = $this->requestData;
        return $this->asJson($form->get());
    }
}