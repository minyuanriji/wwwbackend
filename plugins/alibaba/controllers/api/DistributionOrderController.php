<?php

namespace app\plugins\alibaba\controllers\api;

use app\controllers\api\ApiController;
use app\controllers\api\filters\LoginFilter;
use app\plugins\alibaba\forms\api\AlibabaDistributionOrderDoSubmitForm;
use app\plugins\alibaba\forms\api\AlibabaDistributionOrderListForm;
use app\plugins\alibaba\forms\api\AlibabaDistributionOrderDetailsForm;
use app\plugins\alibaba\forms\api\AlibabaDistributionOrderLogisticsForm;
use app\plugins\alibaba\forms\api\AlibabaDistributionOrderPreviewForm;

class DistributionOrderController extends ApiController {

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
        $form = new AlibabaDistributionOrderDoSubmitForm();
        $form->attributes = $this->requestData;
        return $this->asJson($form->submit());
    }

    /**
     * 订单预览
     * @return \yii\web\Response
     */
    public function actionPreview(){
        $form = new AlibabaDistributionOrderPreviewForm();
        $form->attributes = $this->requestData;
        return $this->asJson($form->preview());
    }

    /**
     * 订单列表
     * @return \yii\web\Response
     */
    public function actionOrderList(){
        $form = new AlibabaDistributionOrderListForm();
        $form->attributes = $this->requestData;
        return $this->asJson($form->getOrderList());
    }

    /**
     * 订单详情
     * @return \yii\web\Response
     */
    public function actionOrderDetails(){
        $form = new AlibabaDistributionOrderDetailsForm();
        $form->attributes = $this->requestData;
        return $this->asJson($form->getOrderDetails());
    }

    /**
     * 物流信息
     * @return \yii\web\Response
     */
    public function actionLogistics(){
        $form = new AlibabaDistributionOrderLogisticsForm();
        $form->attributes = $this->requestData;
        return $this->asJson($form->get());
    }
}