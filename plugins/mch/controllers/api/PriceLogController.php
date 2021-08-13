<?php

namespace app\plugins\mch\controllers\api;


use app\mch\controllers\api\MchMApiController;
use app\plugins\mch\forms\api\MchPriceLogListForm;

class PriceLogController extends MchMApiController{

    /**
     * 结算记录
     * @return \yii\web\Response
     */
    public function actionList(){
        $form = new MchPriceLogListForm();
        $form->attributes = $this->requestData;
        $form->mch_id = $this->mch_id;
        return $this->asJson($form->getList());
    }

}