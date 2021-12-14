<?php

namespace app\plugins\oil\controllers\mall;

use app\plugins\Controller;
use app\plugins\oil\forms\mall\OilOrderListForm;
use app\plugins\oil\forms\mall\OilOrderUpdateStatusForm;

class OrderController extends Controller{

    /**
     * 平台列表
     * @return string|\yii\web\Response
     */
    public function actionList(){
        if (\Yii::$app->request->isAjax) {
            $form = new OilOrderListForm();
            $form->attributes = \Yii::$app->request->get();
            return $this->asJson($form->getList());
        } else {
            return $this->render('index');
        }
    }

    /**
     * 更新订单
     * @return string|\yii\web\Response
     */
    public function actionUpdateStatus(){
        $form = new OilOrderUpdateStatusForm();
        $form->attributes = \Yii::$app->request->post();
        return $this->asJson($form->update());
    }
}