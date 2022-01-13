<?php

namespace app\plugins\smart_shop\controllers\mall;

use app\plugins\Controller;
use app\plugins\smart_shop\forms\mall\OrderDoSplitForm;
use app\plugins\smart_shop\forms\mall\OrderListForm;
use app\plugins\smart_shop\forms\mall\OrderSplitInfoForm;

class OrderController extends Controller{

    /**
     * @Note:分账商户
     * @return string|\yii\web\Response
     */
    public function actionIndex() {

        if (\Yii::$app->request->isAjax) {
            $form = new OrderListForm();
            $form->attributes = \Yii::$app->request->get();
            return $this->asJson($form->getList());
        } else {
            return $this->render('index');
        }
    }

    /**
     * @Note:分账详情
     * @return string|\yii\web\Response
     */
    public function actionSplitInfo(){
        $form = new OrderSplitInfoForm();
        $form->attributes = \Yii::$app->request->get();
        return $this->asJson($form->getInfo());
    }

    /**
     * @Note:确认分账
     * @return string|\yii\web\Response
     */
    public function actionDoSplit(){
        $form = new OrderDoSplitForm();
        $form->attributes = \Yii::$app->request->post();
        return $this->asJson($form->split());
    }
}