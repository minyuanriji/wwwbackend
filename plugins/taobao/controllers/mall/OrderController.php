<?php

namespace app\plugins\taobao\controllers\mall;

use app\plugins\Controller;
use app\plugins\taobao\forms\mall\TaobaoOrderListForm;

class OrderController extends Controller{

    /**
     * 订单管理
     * @return string|\yii\web\Response
     */
    public function actionIndex(){
        if (\Yii::$app->request->isAjax) {
            $form = new TaobaoOrderListForm();
            $form->attributes = \Yii::$app->request->get();
            return $this->asJson($form->getList());
        } else {
            return $this->render('index');
        }
    }

}