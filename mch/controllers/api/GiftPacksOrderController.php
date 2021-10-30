<?php

namespace app\mch\controllers\api;

use app\controllers\api\ApiController;
use app\mch\forms\api\MchManaGiftPacksOrderForm;

class GiftPacksOrderController extends MchMApiController
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
        $form->attributes = $this->requestData;
        $form->store_id = $this->mchData['store']['id'];
        return $this->asJson($form->getOrderList());
    }
}