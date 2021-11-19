<?php

namespace app\mch\controllers\api;

use app\plugins\mch\forms\api\mana\MchManaOrderGiftPacksListForm;

/**
 * @deprecated
 */
class OrderController extends MchMApiController{

    /**
     * 本地生活订单
     * @return \yii\web\Response
     */
    public function actionGiftpacks(){
        $form = new MchManaOrderGiftPacksListForm();
        $form->attributes = $this->requestData;
        $form->mch_id     = $this->mch_id;
        return $this->asJson($form->getList());
    }

}