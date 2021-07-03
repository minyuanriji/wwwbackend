<?php
namespace app\controllers\mall;

use app\forms\mall\order\OrderClerkDetailForm;
use app\forms\mall\order\OrderClerkListForm;
use app\forms\mall\order\OrderClerkSendDetailListForm;
use app\forms\mall\order\OrderClerkSendForm;
use app\forms\mall\order\OrderClerkStoreForm;

class OrderClerkController extends MallController{

    public function actionStore(){

        if (\Yii::$app->request->isAjax) {
            $form = new OrderClerkStoreForm();
            $form->attributes = \Yii::$app->request->get();
            return $this->asJson($form->getList());
        } else {
            return $this->render('store');
        }
    }

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
     * 待补货记录列表
     * @return string|\yii\web\Response
     */
    public function actionSendDetailList(){
        $form = new OrderClerkSendDetailListForm();
        $form->attributes = \Yii::$app->request->get();
        return $this->asJson($form->getList());
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

}