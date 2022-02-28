<?php

namespace app\plugins\shopping_voucher\controllers\mall;

use app\plugins\shopping_voucher\forms\mall\FromAddcreditEditForm;
use app\plugins\shopping_voucher\forms\mall\FromAddcreditListForm;
use app\plugins\Controller;

class FromAddcreditController extends Controller
{
    /**
     * @Note:话费赠送红包设置
     * @return string|\yii\web\Response
     */
    public function actionEdit()
    {
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isPost) {
                $form = new FromAddcreditEditForm();
                $form->attributes = \Yii::$app->request->post('form');
                return $this->asJson($form->save());
            } else {
                $form = new FromAddcreditListForm();
                $form->attributes = \Yii::$app->request->get();
                $detail = $form->getDetail();
                return $this->asJson($detail);
            }
        } else {
            return $this->render('edit');
        }

    }
}