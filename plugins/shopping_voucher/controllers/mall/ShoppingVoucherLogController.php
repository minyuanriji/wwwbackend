<?php

namespace app\plugins\shopping_voucher\controllers\mall;

use app\plugins\Controller;
use app\plugins\shopping_voucher\forms\mall\ShoppingVoucherLogListForm;
use app\plugins\shopping_voucher\forms\mall\ShoppingVoucherRechargeForm;


class ShoppingVoucherLogController extends Controller{

    /**
     * 购物券记录
     * @return bool|string|\yii\web\Response
     */
    public function actionList(){
        if (\Yii::$app->request->isAjax) {
            $form = new ShoppingVoucherLogListForm();
            $form->attributes = \Yii::$app->request->get();
            return $this->asJson($form->getList());
        } else {
            return $this->render('list');
        }
    }

    /**
     * 购物券记录统计
     * @return bool|string|\yii\web\Response
     */
    public function actionStatistics(){
        $form = new ShoppingVoucherLogListForm();
        $form->attributes = \Yii::$app->request->get();
        return $this->asJson($form->statistics());
    }

    /**
     * 购物券充值
     * @return bool|string|\yii\web\Response
     */
    public function actionRecharge(){
        $form = new ShoppingVoucherRechargeForm();
        $form->attributes = \Yii::$app->request->post();
        return $this->asJson($form->recharge());
    }
}