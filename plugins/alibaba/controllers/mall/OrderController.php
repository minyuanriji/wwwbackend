<?php

namespace app\plugins\alibaba\controllers\mall;

use app\plugins\alibaba\forms\mall\AlibabaDistributionOrderListForm;
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
}