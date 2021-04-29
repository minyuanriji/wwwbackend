<?php
namespace app\plugins\mch\controllers\mall;

use app\plugins\Controller;
use app\plugins\mch\forms\mall\DistributionDetailForm;
use app\plugins\mch\forms\mall\DistributionForm;

class DistributionController extends Controller{

    public function actionList(){
        return $this->render('index');
    }

    public function actionEdit(){
        if (\Yii::$app->request->isAjax) {
            if(\Yii::$app->request->isPost){
                $form = new DistributionForm();
                $form->attributes = \Yii::$app->request->post();
                return $this->asJson($form->save());
            }else{
                $form = new DistributionDetailForm();
                $form->attributes = \Yii::$app->request->get();
                return $this->asJson($form->getDetail());
            }
        }else{
            return $this->render('edit');
        }
    }
}