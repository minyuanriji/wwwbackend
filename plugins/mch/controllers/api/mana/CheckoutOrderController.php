<?php

namespace app\plugins\mch\controllers\api\mana;

use app\plugins\mch\forms\api\mana\MchManaCheckoutOrderQrcodeForm;

class CheckoutOrderController extends MchAdminController {

    /**
     * 生成结账单
     * @return \yii\web\Response
     * @throws \app\core\exceptions\ClassNotFoundException
     * @throws \yii\base\Exception
     * @throws \yii\db\Exception
     */
    public function actionQrcode(){
        $form = new MchManaCheckoutOrderQrcodeForm();
        $form->attributes = $this->requestData;
        return $this->asJson($form->getQrcode());
    }

}