<?php

namespace app\plugins\smart_shop\controllers\api\store_admin;

use app\plugins\smart_shop\controllers\api\AdminAuthController;
use app\plugins\smart_shop\forms\api\store_admin\StoreSetDetailForm;

class StoreSetController extends AdminAuthController {

    public function actionIndex(){
        $form = new StoreSetDetailForm();
        $form->attributes  = $this->requestData;
        $form->merchant_id = $this->merchant ? $this->merchant['id'] : 0;
        $form->store_id    = $this->store ? $this->store['id'] : '';
        return $this->asJson($form->get());
    }

}