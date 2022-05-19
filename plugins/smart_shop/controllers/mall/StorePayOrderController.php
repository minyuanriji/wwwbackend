<?php

namespace app\plugins\smart_shop\controllers\mall;

use app\plugins\Controller;
use app\plugins\smart_shop\forms\mall\StorePayOrderListForm;

class StorePayOrderController extends Controller{

    /**
     * @Note:门店支付单记录
     * @return string|\yii\web\Response
     */
    public function actionIndex() {
        if (\Yii::$app->request->isAjax) {
            $form = new StorePayOrderListForm();
            $form->attributes = \Yii::$app->request->get();
            return $this->asJson($form->getList());
        } else {
            return $this->render('index');
        }
    }

}