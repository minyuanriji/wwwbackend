<?php

namespace app\plugins\hotel\controllers\mall;

use app\plugins\Controller;
use app\plugins\hotel\forms\mall\PlatformsGetSettingForm;
use app\plugins\hotel\forms\mall\PlatformsSettingSaveForm;

class PlatformsController extends Controller{

    /**
     * 平台配置
     * @return string|\yii\web\Response
     */
    public function actionSetting(){
        if (\Yii::$app->request->isAjax) {
            if(\Yii::$app->request->isPost){
                $form = new PlatformsSettingSaveForm();
                $form->attributes = \Yii::$app->request->post();
                return $this->asJson($form->save());
            }else{
                $form = new PlatformsGetSettingForm();
                $form->attributes = \Yii::$app->request->get();
                return $this->asJson($form->get());
            }
        }else{
            return $this->render('setting');
        }
    }

}