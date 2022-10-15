<?php
namespace app\plugins\income_log\controllers\mall;

use app\core\ApiCode;
use app\plugins\Controller;
use app\plugins\income_log\forms\mall\SettingSaveForm;
use app\plugins\income_log\models\Setting;

class SettingController extends Controller{

    /**
     * 基本设置
     * @return string|\yii\web\Response
     * @throws \Exception
     */
    public function actionIndex(){
        if (\Yii::$app->request->isAjax) {
            if(\Yii::$app->request->isPost){
                $form = new SettingSaveForm();
                $form->attributes = \Yii::$app->request->post();
                return $this->asJson($form->save());
            }else{
                return $this->asJson([
                    'code' => ApiCode::CODE_SUCCESS,
                    'data' => Setting::getSettings(),
                    'msg'  => 'ok'
                ]);
            }
        }else{
            return $this->render('index');
        }
    }
}