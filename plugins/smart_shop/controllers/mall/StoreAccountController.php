<?php

namespace app\plugins\smart_shop\controllers\mall;

use app\plugins\Controller;
use app\plugins\smart_shop\forms\mall\StoreAccountLogForm;

class StoreAccountController extends Controller{

    public function actionLog(){
        if (\Yii::$app->request->isAjax) {
            $form = new StoreAccountLogForm();
            $form->attributes = \Yii::$app->request->get();
            return $this->asJson($form->getList());
        } else {
            return $this->render('log');
        }
    }

}