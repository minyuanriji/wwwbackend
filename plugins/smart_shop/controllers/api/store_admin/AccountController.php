<?php

namespace app\plugins\smart_shop\controllers\api\store_admin;

use app\plugins\smart_shop\controllers\api\AdminAuthController;
use app\plugins\smart_shop\forms\api\store_admin\AccountAccountDetailForm;
use app\plugins\smart_shop\forms\api\store_admin\AccountRechargeLogForm;
use app\plugins\smart_shop\forms\api\store_admin\AccountRechargeSubmitForm;

class AccountController extends AdminAuthController {

    /**
     * 账户详情
     * @return \yii\web\Response
     */
    public function actionDetail(){
        $form = new AccountAccountDetailForm();
        $form->attributes  = $this->requestData;
        $form->merchant_id = $this->merchant ? $this->merchant['id'] : 0;
        $form->store_id    = $this->store ? $this->store['id'] : '';
        return $this->asJson($form->getDetail());
    }

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

    /**
     * 充值记录
     * @return \yii\web\Response
     */
    public function actionRechargeLog(){
        $form = new AccountRechargeLogForm();
        $form->attributes  = $this->requestData;
        $form->merchant_id = $this->merchant ? $this->merchant['id'] : 0;
        $form->store_id    = $this->store ? $this->store['id'] : 0;
        return $this->asJson($form->getList());
    }
}