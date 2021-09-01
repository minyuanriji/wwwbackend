<?php

namespace app\plugins\shopping_voucher\controllers\mall;

use app\plugins\shopping_voucher\forms\mall\VoucherLogListForm;
use app\plugins\integral_card\controllers\BaseController;


class VoucherController extends BaseController
{
    /**
     * @Note:购物券记录
     * @return bool|string|\yii\web\Response
     */
    public function actionVoucherLog()
    {
        if (\Yii::$app->request->isAjax) {
            $form = new VoucherLogListForm();
            $form->attributes = \Yii::$app->request->get();
            return $this->asJson($form->getList());
        } else {
            return $this->render('voucher_log');
        }
    }
}