<?php

namespace app\plugins\smart_shop\controllers\api\kpi_admin;

use app\plugins\smart_shop\forms\api\kpi_admin\KpiAdminStatNewOrderForm;
use app\plugins\smart_shop\forms\api\kpi_admin\KpiAdminStatRegisterForm;
use app\plugins\smart_shop\forms\api\kpi_admin\KpiAdminStatShareForm;

class StatController extends AdminAuthController {

    /**
     * 新订单
     * @return \yii\web\Response
     */
    public function actionNewOrder(){
        $form = new KpiAdminStatNewOrderForm();
        $form->attributes  = $this->requestData;
        $form->merchant_id = $this->merchant ? $this->merchant['id'] : 0;
        $form->store_id    = $this->store ? $this->store['id'] : 0;
        return $this->asJson($form->getList());
    }

    /**
     * 新用户
     * @return \yii\web\Response
     */
    public function actionRegister(){
        $form = new KpiAdminStatRegisterForm();
        $form->attributes  = $this->requestData;
        $form->merchant_id = $this->merchant ? $this->merchant['id'] : 0;
        $form->store_id    = $this->store ? $this->store['id'] : 0;
        return $this->asJson($form->getList());
    }

    /**
     * 分享统计
     * @return \yii\web\Response
     */
    public function actionShare(){
        $form = new KpiAdminStatShareForm();
        $form->attributes  = $this->requestData;
        $form->merchant_id = $this->merchant ? $this->merchant['id'] : 0;
        $form->store_id    = $this->store ? $this->store['id'] : 0;
        return $this->asJson($form->getList());
    }
}