<?php
namespace app\controllers\mall;

use app\forms\mall\order\OrderClerkDetailForm;
use app\forms\mall\order\OrderClerkListForm;
use app\forms\mall\order\OrderClerkSendForm;
use app\forms\mall\order\OrderClerkUpdateExpressStatusForm;

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

    /**
     * 核销记录详情
     * @return string|\yii\web\Response
     */
    public function actionDetail(){
        if (\Yii::$app->request->isAjax) {
            $form = new OrderClerkDetailForm();
            $form->attributes = \Yii::$app->request->get();
            return $this->asJson($form->getDetail());
        } else {
            return $this->render('detail');
        }
    }

    /**
     * 补货
     * @return \yii\web\Response
     */
    public function actionSend(){
        $form = new OrderClerkSendForm();
        $form->attributes = \Yii::$app->request->post();
        return $this->asJson($form->send());
    }


    public function actionUpdateExpressStatus(){
        $form = new OrderClerkUpdateExpressStatusForm();
        $form->attributes = \Yii::$app->request->post();
        return $this->asJson($form->update());
    }
}