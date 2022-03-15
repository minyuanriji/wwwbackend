<?php

namespace app\plugins\smart_shop\controllers\mall;

use app\plugins\Controller;

class MpController extends Controller{

    /**
     * @Note: 微信小程序管理
     * @return string|\yii\web\Response
     */
    public function actionWechat() {
        if (\Yii::$app->request->isAjax) {
            $form = new MpWechatListForm();
            $form->attributes = \Yii::$app->request->get();
            return $this->asJson($form->getList());
        } else {
            return $this->render('wechat');
        }
    }

    /**
     * @Note: 支付宝小程序管理
     * @return string|\yii\web\Response
     */
    public function actionAlipay() {
        if (\Yii::$app->request->isAjax) {
            $form = new MpAlipayListForm();
            $form->attributes = \Yii::$app->request->get();
            return $this->asJson($form->getList());
        } else {
            return $this->render('alipay');
        }
    }

}