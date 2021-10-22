<?php
namespace app\controllers\mall;

use app\forms\mall\finance\MchCashApplyForm;
use app\forms\mall\finance\MchCashListForm;

class MchCashController extends MallController{

    public function actionIndex(){

        if (\Yii::$app->request->isAjax) {
            $form = new MchCashListForm();
            $form->attributes = \Yii::$app->request->get();
            return $this->asJson($form->getList());
        } else {
            if (\Yii::$app->request->post('flag') === 'EXPORT') {
                $fields = explode(',', \Yii::$app->request->post('fields'));
                $form = new MchCashListForm();
                $form->attributes = \Yii::$app->request->post();
                $form->fields = $fields;
                $form->getList();
                return false;
            } else {
                return $this->render('index');
            }
        }
    }

    public function actionApply(){
        $form = new MchCashApplyForm();
        $form->attributes = \Yii::$app->request->post();
        return $this->asJson($form->save());
    }

    public function actionStatistics(){
        $form = new MchCashListForm();
        $form->attributes = \Yii::$app->request->get();
        return $this->asJson($form->statistics());
    }

}