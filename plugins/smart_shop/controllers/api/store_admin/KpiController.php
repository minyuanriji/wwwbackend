<?php

namespace app\plugins\smart_shop\controllers\api\store_admin;

use app\plugins\smart_shop\controllers\api\AdminAuthController;
use app\plugins\smart_shop\forms\api\store_admin\KpiDeleteRuleForm;
use app\plugins\smart_shop\forms\api\store_admin\KpiGetRuleDetailForm;
use app\plugins\smart_shop\forms\api\store_admin\KpiGetRuleListForm;
use app\plugins\smart_shop\forms\api\store_admin\KpiSaveRuleForm;

class KpiController extends AdminAuthController {

    /**
     * 保存规则
     * @return \yii\web\Response
     */
    public function actionSaveRule(){
        $form = new KpiSaveRuleForm();
        $form->attributes = $this->requestData;
        $form->merchant_id = $this->merchant ? $this->merchant['id'] : 0;
        $form->store_id    = $this->store ? $this->store['id'] : 0;
        return $this->asJson($form->save());
    }

    /**
     * 获取规则列表
     * @return \yii\web\Response
     */
    public function actionGetRuleList(){
        $form = new KpiGetRuleListForm();
        $form->attributes = $this->requestData;
        $form->merchant_id = $this->merchant ? $this->merchant['id'] : 0;
        $form->store_id    = $this->store ? $this->store['id'] : 0;
        return $this->asJson($form->getList());
    }

    /**
     * 获取规则详情
     * @return \yii\web\Response
     */
    public function actionGetRuleDetail(){
        $form = new KpiGetRuleDetailForm();
        $form->attributes = $this->requestData;
        $form->merchant_id = $this->merchant ? $this->merchant['id'] : 0;
        $form->store_id    = $this->store ? $this->store['id'] : 0;
        return $this->asJson($form->getDetail());
    }

    /**
     * 删除规则
     * @return \yii\web\Response
     */
    public function actionDeleteRule(){
        $form = new KpiDeleteRuleForm();
        $form->attributes = $this->requestData;
        $form->merchant_id = $this->merchant ? $this->merchant['id'] : 0;
        $form->store_id    = $this->store ? $this->store['id'] : 0;
        return $this->asJson($form->delete());
    }
}