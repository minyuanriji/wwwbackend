<?php

namespace app\plugins\mch\controllers\api\mana;

use app\plugins\mch\forms\api\mana\MchManaGiftPacksOrderForm;

class GiftPacksOrderController extends MchAdminController
{
    /**
     * 商户大礼包订单
     * @return \yii\web\Response
     * @throws \app\core\exceptions\ClassNotFoundException
     * @throws \yii\base\Exception
     * @throws \yii\db\Exception
     */
    public function actionIndex()
    {
        $form = new MchManaGiftPacksOrderForm();
        return $this->asJson($form->getOrderList());
    }

}