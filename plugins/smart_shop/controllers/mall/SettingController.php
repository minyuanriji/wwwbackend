<?php
namespace app\plugins\smart_shop\controllers\mall;

use app\plugins\Controller;
use app\plugins\smart_shop\forms\mall\SettingDetailForm;
use app\plugins\smart_shop\forms\mall\SettingSaveForm;

class SettingController extends Controller{

    /**
     * @Note:全局配置
     * @return string|\yii\web\Response
     */
    public function actionIndex() {
        if (\Yii::$app->request->isAjax) {
            $form = new SettingDetailForm();
            $form->attributes = \Yii::$app->request->get();
            return $this->asJson($form->getDetail());
        } else {
            return $this->render('index');
        }
    }

    /**
     * 保存数据库信息
     * @return string|\yii\web\Response
     */
    public function actionSave(){
        $form = new SettingSaveForm();
        $form->attributes = \Yii::$app->request->post();
        return $this->asJson($form->save());
    }
}