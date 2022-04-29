<?php

namespace app\plugins\smart_shop\controllers\api\kpi_admin;

use app\plugins\smart_shop\forms\api\kpi_admin\KpiAdminSettingDetailForm;
use app\plugins\smart_shop\forms\api\kpi_admin\KpiAdminSettingSaveForm;

class SettingController extends AdminAuthController {

    /**
     * 保存KPI奖励设置
     * @return \yii\web\Response
     */
    public function actionSave(){
        $form = new KpiAdminSettingSaveForm();
        $form->attributes = $this->requestData;
        $form->merchant_id = $this->merchant ? $this->merchant['id'] : 0;
        return $this->asJson($form->save());
    }

    /**
     * 获取KPI奖励设置
     * @return \yii\web\Response
     */
    public function actionDetail(){
        $form = new KpiAdminSettingDetailForm();
        $form->attributes = $this->requestData;
        $form->merchant_id = $this->merchant ? $this->merchant['id'] : 0;
        return $this->asJson($form->getDetail());
    }

}