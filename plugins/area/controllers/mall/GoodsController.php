<?php

namespace app\plugins\area\controllers\mall;

use app\plugins\area\forms\mall\AreaGoodsForm;
use app\plugins\Controller;

class GoodsController extends Controller
{
    public function actionAreaSetting()
    {
        if (\Yii::$app->request->isAjax) {

            if (\Yii::$app->request->isGet) {
                $form = new AreaGoodsForm();
                return $this->asJson($form->getAreaSetting());
            }
        }
    }


    public function actionGoodsSetting()
    {
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isPost) {
                $form = new AreaGoodsForm();
                $form->attributes = \Yii::$app->request->post()['form'];

                return $this->asJson($form->saveAreaGoodsSetting());
            } else {
                $form = new AreaGoodsForm();
                $form->attributes = \Yii::$app->request->get();
                return $this->asJson($form->getAreaGoodsSetting());
            }
        } else {
            return $this->render('edit');
        }
    }

}