<?php
namespace app\mch\controllers;


use app\forms\mall\refund_setting\RefundAddressEditForm;
use app\forms\mall\refund_setting\RefundAddressForm;

class RefundSettingController extends MchController {

    /**
     * 添加、编辑
     * @return string|\yii\web\Response
     */
    public function actionEdit(){
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isPost) {
                $form = new RefundAddressEditForm();
                $form->attributes = \Yii::$app->request->post('form');
                $res = $form->save();

                return $this->asJson($res);
            } else {
                $form = new RefundAddressForm();
                $form->attributes = \Yii::$app->request->get();
                $detail = $form->getDetail();

                return $this->asJson($detail);
            }
        } else {
            return $this->render('edit');
        }
    }

}