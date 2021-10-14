<?php

namespace app\plugins\alibaba\controllers\mall;

use app\plugins\alibaba\forms\mall\AlibabaDistributionOrderListForm;
use app\plugins\alibaba\forms\mall\AlibabaDistributionOrderRefundListForm;
use app\plugins\alibaba\forms\mall\AlibabaDistributionAfterApplyForm;
use app\plugins\alibaba\forms\mall\AlibabaDistributionSalePaymentForm;
use app\plugins\alibaba\forms\mall\AlibabaDistributionPaymentInfoForm;
use app\plugins\Controller;

class OrderController extends Controller
{
    /**
     * 订单列表
     * @return string|\yii\web\Response
     */
    public function actionIndex()
    {
        if (\Yii::$app->request->isAjax) {
            $form = new AlibabaDistributionOrderListForm();
            $form->attributes = \Yii::$app->request->get();
            return $this->asJson($form->getList());
        } else {
            return $this->render('index');
        }
    }

    /**
     * 售后订单列表
     * @return string|\yii\web\Response
     */
    public function actionRefundList()
    {
        if (\Yii::$app->request->isAjax) {
            $form = new AlibabaDistributionOrderRefundListForm();
            $form->attributes = \Yii::$app->request->get();
            return $this->asJson($form->getList());
        } else {
            return $this->render('refund-list');
        }
    }

    /**
     * 售后申请
     * @return string|\yii\web\Response
     */
    public function actionApply()
    {
        $form = new AlibabaDistributionAfterApplyForm();
        $form->attributes = \Yii::$app->request->post();
        return $this->asJson($form->save());
    }

    /**
     * 售后订单打款
     * @return string|\yii\web\Response
     */
    public function actionSalePayment()
    {
        $form = new AlibabaDistributionSalePaymentForm();
        $form->attributes = \Yii::$app->request->post();
        return $this->asJson($form->save());
    }

    /**
     * 打款操作展示详情
     * @return string|\yii\web\Response
     */
    public function actionPaymentInfo()
    {
        $form = new AlibabaDistributionPaymentInfoForm();
        $form->attributes = \Yii::$app->request->get();
        return $this->asJson($form->getInfo());
    }
}