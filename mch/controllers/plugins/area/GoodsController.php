<?php
namespace app\mch\controllers\plugins\area;

use app\mch\controllers\MchController;
use app\plugins\area\forms\mall\AreaGoodsForm;

class GoodsController extends MchController {

    public function actionGoodsSetting(){
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