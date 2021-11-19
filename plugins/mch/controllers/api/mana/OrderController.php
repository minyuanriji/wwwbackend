<?php

namespace app\plugins\mch\controllers\api\mana;

use app\plugins\mch\forms\api\mana\MchManaOrderGiftPacksListForm;

class OrderController extends MchAdminController {

    /**
     * 本地生活订单
     * @return \yii\web\Response
     */
    public function actionGiftpacks(){
        $form = new MchManaOrderGiftPacksListForm();
        $form->attributes = $this->requestData;
        return $this->asJson($form->getList());
    }

}