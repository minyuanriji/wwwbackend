<?php

namespace app\plugins\smart_shop\controllers\api\store_admin;

use app\plugins\smart_shop\controllers\api\AdminAuthController;
use app\plugins\smart_shop\forms\api\store_admin\KpiKpiUserListForm;
use app\plugins\smart_shop\forms\api\store_admin\KpiUserDeleteForm;
use app\plugins\smart_shop\forms\api\store_admin\KpiUserEditForm;

class KpiUserController extends AdminAuthController {

    /**
     * 获取员工列表
     * @return \yii\web\Response
     */
    public function actionList(){
        $form = new KpiKpiUserListForm();
        $form->attributes = $this->requestData;
        $form->merchant_id = $this->merchant ? $this->merchant['id'] : 0;
        $form->store_id    = $this->store ? $this->store['id'] : 0;
        return $this->asJson($form->getList());
    }

    /**
     * 保存规则
     * @return \yii\web\Response
     */
    public function actionEdit(){
        $form = new KpiUserEditForm();
        $form->attributes = $this->requestData;
        $form->merchant_id = $this->merchant ? $this->merchant['id'] : 0;
        $form->store_id    = $this->store ? $this->store['id'] : 0;
        return $this->asJson($form->save());
    }

    /**
     * 删除
     * @return \yii\web\Response
     */
    public function actionDelete(){
        $form = new KpiUserDeleteForm();
        $form->attributes = $this->requestData;
        $form->merchant_id = $this->merchant ? $this->merchant['id'] : 0;
        $form->store_id    = $this->store ? $this->store['id'] : 0;
        return $this->asJson($form->delete());
    }
}