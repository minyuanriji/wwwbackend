<?php

namespace app\plugins\addcredit\controllers\mall\order;

use app\plugins\Controller;
use app\plugins\addcredit\forms\mall\PhoneBillOrderListForm;

class OrderController extends Controller
{
    /**
     * 话费订单列表
     * @return string|\yii\web\Response
     */
    public function actionIndex ()
    {
        if (\Yii::$app->request->isAjax) {
            $form = new PhoneBillOrderListForm();
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
    public function actionRefundList ()
    {
        if (\Yii::$app->request->isAjax) {
            $form = new PhoneBillOrderListForm();
            $form->attributes = \Yii::$app->request->get();
            return $this->asJson($form->getRefundList());
        } else {
            return $this->render('order-refund');
        }
    }
}