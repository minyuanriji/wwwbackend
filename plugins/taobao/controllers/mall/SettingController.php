<?php

namespace app\plugins\taobao\controllers\mall;

use app\plugins\Controller;
use app\plugins\taobao\forms\mall\TaobaoSettingEditForm;

class SettingController extends Controller{

    public function actionIndex(){
        if(\Yii::$app->getRequest()->isAjax){
            $form = new TaobaoSettingEditForm();
            $form->attributes = \Yii::$app->request->get();
            return $this->asJson($form->settings());
        }else{
            return $this->render('index');
        }
    }

    /**
     * 保存设置
     * @return \yii\web\Response
     */
    public function actionSave(){
        $form = new TaobaoSettingEditForm();
        $form->attributes = \Yii::$app->request->post();
        return $this->asJson($form->save());
    }
}