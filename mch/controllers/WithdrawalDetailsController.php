<?php
namespace app\mch\controllers;

use app\mch\forms\mch\MchCashForm;

class WithdrawalDetailsController extends BaseController
{
    //提现明细
    public function actionMchAllList ()
    {
        $form = new MchCashForm();
        $form->attributes = \Yii::$app->request->get();
        return $this->asJson($form->getList());
    }
}
