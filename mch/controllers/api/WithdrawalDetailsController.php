<?php

namespace app\mch\controllers\api;

use app\mch\forms\mch\MchCashForm;

class WithdrawalDetailsController extends MchMApiController{
    //提现明细
    public function actionMchAllList (){
        $form = new MchCashForm();
        $mch_id = $this->mch_id;
        return $form->getList($mch_id);
    }
}
