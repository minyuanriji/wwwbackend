<?php

namespace app\plugins\giftpacks\controllers\mall;

use app\plugins\Controller;
use app\plugins\giftpacks\forms\mall\order\GiftpacksGroupOrderInfoForm;
use app\plugins\giftpacks\forms\mall\order\GiftpacksGroupOrderListForm;

class GiftpacksGroupOrderController extends Controller
{
    /**
     * 大礼包拼团订单列表
     * @return string|\yii\web\Response
     */
    public function actionGroupIndex()
    {
        if (\Yii::$app->request->isAjax) {
            $form = new GiftpacksGroupOrderListForm();
            $form->attributes = \Yii::$app->request->get();
            return $this->asJson($form->getList());
        } else {
            return $this->render('index');
        }
    }

    /**
     * 大礼包拼团订单详情
     * @return string|\yii\web\Response
     */
    public function actionGroupInfo()
    {
        $form = new GiftpacksGroupOrderInfoForm();
        $form->attributes = \Yii::$app->request->get();
        return $this->asJson($form->getList());
    }
}