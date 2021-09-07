<?php

namespace app\plugins\giftpacks\controllers\mall;

use app\plugins\Controller;
use app\plugins\giftpacks\forms\mall\order\GiftpacksOrderListForm;

class GiftpacksOrderController extends Controller
{
    /**
     * 大礼包订单列表
     * @return string|\yii\web\Response
     */
    public function actionIndex()
    {
        if (\Yii::$app->request->isAjax) {
            $form = new GiftpacksOrderListForm();
            $form->attributes = \Yii::$app->request->get();
            return $this->asJson($form->getList());
        } else {
            return $this->render('index');
        }
    }
}