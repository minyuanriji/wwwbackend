<?php

namespace app\plugins\smart_shop\controllers\api\store_admin;

use app\plugins\smart_shop\controllers\api\AdminAuthController;
use app\plugins\smart_shop\forms\api\store_admin\StoreSetDetailForm;
use app\plugins\smart_shop\forms\api\store_admin\StoreSetSaveForm;

class StoreSetController extends AdminAuthController {

    /**
     * 获取配置
     * @return \yii\web\Response
     */
    public function actionIndex(){
        $form = new StoreSetDetailForm();
        $form->attributes  = $this->requestData;
        $form->merchant_id = $this->merchant ? $this->merchant['id'] : 0;
        $form->store_id    = $this->store ? $this->store['id'] : '';
        return $this->asJson($form->get());
    }

    /**
     * 保存配置
     * @return \yii\web\Response
     */
    public function actionSave(){
        $form = new StoreSetSaveForm();
        $form->attributes  = $this->requestData;
        $form->merchant_id = $this->merchant ? $this->merchant['id'] : 0;
        $form->store_id    = $this->store ? $this->store['id'] : '';
        return $this->asJson($form->save());
    }
}