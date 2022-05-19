<?php

namespace app\plugins\smart_shop\controllers\api;

use app\controllers\api\ApiController;
use app\plugins\smart_shop\forms\api\StorePayOrderDetailForm;

class StorePayOrderController extends ApiController {

    /**
     * 获取支付单详情
     * @return string|\yii\web\Response
     */
    public function actionDetail(){
        $form = new StorePayOrderDetailForm();
        $form->attributes = $this->requestData;
        return $this->asJson($form->getDetail());
    }

}