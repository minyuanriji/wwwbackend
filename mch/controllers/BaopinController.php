<?php
namespace app\mch\controllers;


use app\mch\forms\baopin\GoodsListForm;

class BaopinController extends MchController {

    /**
     * 爆品记录
     * @return bool|string|\yii\web\Response
     */
    public function actionIndex(){
        if (\Yii::$app->request->isAjax) {
            $form = new GoodsListForm();
            $form->attributes = \Yii::$app->request->get();
            $form->mch_id     = \Yii::$app->mchId;
            return $this->asJson($form->getList());
        } else {
            return $this->render('index');
        }
    }

}