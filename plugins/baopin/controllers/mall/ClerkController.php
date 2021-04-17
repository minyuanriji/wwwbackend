<?php


namespace app\plugins\baopin\controllers\mall;


use app\plugins\baopin\forms\mall\ClerkListForm;
use app\plugins\Controller;

class ClerkController extends Controller{

    public function actionList(){
        if (\Yii::$app->request->isAjax) {
            $form = new ClerkListForm();
            $form->attributes = \Yii::$app->request->get();
            return $this->asJson($form->getList());
        } else {
            return $this->render('index');
        }
    }

}