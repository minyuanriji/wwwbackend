<?php
namespace app\mch\controllers;

use app\mch\forms\mch\MchCashForm;

class WithdrawalDetailsController extends BaseController
{
    //提现明细
    public function actionMchAllList ()
    {
        $form = new MchCashForm();
        return $form->getList();
    }
}
