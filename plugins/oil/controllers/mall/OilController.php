<?php

namespace app\plugins\oil\controllers\mall;

use app\plugins\Controller;
use app\plugins\oil\forms\mall\OilSettingLoadForm;
use app\plugins\oil\forms\mall\OilSettingUpdateForm;

class OilController extends Controller{

    public function actionSetting(){
        if (\Yii::$app->request->isAjax) {
            if(\Yii::$app->request->isPost){
                $form = new OilSettingUpdateForm();
                $form->attributes = \Yii::$app->request->post();
                return $this->asJson($form->save());
            }else{
                $form = new OilSettingLoadForm();
                $form->attributes = \Yii::$app->request->get();
                return $this->asJson($form->get());
            }
        } else {
            return $this->render('setting');
        }
    }

}