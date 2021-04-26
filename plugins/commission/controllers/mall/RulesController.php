<?php
namespace app\plugins\commission\controllers\mall;


use app\plugins\commission\forms\CommissionRuleListForm;
use app\plugins\Controller;

class RulesController extends Controller{

    public function actionIndex(){
        if (\Yii::$app->request->isAjax) {
            $form = new CommissionRuleListForm();
            $form->attributes = \Yii::$app->request->get();
            return $this->asJson($form->getList());
        } else {
            return $this->render('index');
        }
    }

    public function actionEdit(){

        return $this->render('edit');
    }

}