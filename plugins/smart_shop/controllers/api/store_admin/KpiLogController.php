<?php

namespace app\plugins\smart_shop\controllers\api\store_admin;

use app\plugins\smart_shop\controllers\api\AdminAuthController;
use app\plugins\smart_shop\forms\api\store_admin\KpiLogQueryForm;
use app\plugins\smart_shop\forms\api\store_admin\KpiLogQueryUserForm;

class KpiLogController extends AdminAuthController {

    /**
     * KPI查询-按商品
     * @return \yii\web\Response
     */
    public function actionQueryUser(){
        $form = new KpiLogQueryUserForm();
        $form->attributes = $this->requestData;
        $form->merchant_id = $this->merchant ? $this->merchant['id'] : 0;
        $form->store_id    = $this->store ? $this->store['id'] : 0;
        return $this->asJson($form->getList());
    }

}