<?php

namespace app\plugins\smart_shop\controllers\api\store_admin;

use app\plugins\smart_shop\controllers\api\AdminAuthController;
use app\plugins\smart_shop\forms\api\store_admin\AccountRechargeSubmitForm;

class AccountController extends AdminAuthController {

    /**
     * 提交充值订单
     * @return \yii\web\Response
     */
    public function actionRechargeSubmit(){
        $form = new AccountRechargeSubmitForm();
        $form->attributes  = $this->requestData;
        $form->merchant_id = $this->merchant ? $this->merchant['id'] : 0;
        $form->store_id    = $this->store ? $this->store['id'] : '';
        return $this->asJson($form->submit());
    }

}