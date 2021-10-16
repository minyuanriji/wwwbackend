<?php

namespace app\plugins\alibaba\controllers\mall;

use app\plugins\alibaba\forms\mall\AlibabaDistributionOrderDetailForm;
use app\plugins\alibaba\forms\mall\AlibabaDistributionOrderListForm;
use app\plugins\alibaba\forms\mall\AlibabaDistributionOrderRefundListForm;
use app\plugins\alibaba\forms\mall\AlibabaDistributionAfterApplyForm;
use app\plugins\alibaba\forms\mall\AlibabaDistributionPaymentInfoForm;
use app\plugins\alibaba\forms\mall\AlibabaDistributionRefundPaidForm;
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
     * 订单详情
     * @return string|\yii\web\Response
     */
    public function actionOrderDetail(){
        $form = new AlibabaDistributionOrderDetailForm();
        $form->attributes = \Yii::$app->request->get();
        return $this->asJson($form->getDetail());
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
     * 售后订单打款
     * @return string|\yii\web\Response
     */
    public function actionRefundPaid(){
        $form = new AlibabaDistributionRefundPaidForm();
        $form->attributes = \Yii::$app->request->post();
        return $this->asJson($form->save());
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