<?php
namespace app\controllers\mall;

use app\forms\mall\order\OrderClerkDetailForm;
use app\forms\mall\order\OrderClerkListForm;

class OrderClerkController extends MallController{

    public function actionIndex(){

        if (\Yii::$app->request->isAjax) {
            $form = new OrderClerkListForm();
            $form->attributes = \Yii::$app->request->get();
            return $this->asJson($form->getList());
        } else {
            return $this->render('index');
        }
    }

    public function actionDetail(){
        if (\Yii::$app->request->isAjax) {
            $form = new OrderClerkDetailForm();
            $form->attributes = \Yii::$app->request->get();
            return $this->asJson($form->getDetail());
        } else {
            return $this->render('detail');
        }
    }

}