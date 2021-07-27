<?php
namespace app\mch\controllers;

use app\core\ApiCode;
use app\mch\forms\admin\LoginForm;

class AdminController extends MchController {

    public $layout = 'main2';

    public function actionLogin(){
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isPost) {
                $form = new LoginForm();
                $form->attributes = \Yii::$app->request->post('form');
                $res = $form->login();
                return $this->asJson($res);
            }
        } else {
            return $this->render('login');
        }
    }

    public function actionLogout()
    {
        $logout = \Yii::$app->mchAdmin->logout();

        return $this->asJson([
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '请求成功',
            'data' => [
                'url' => 'mch/admin/login'
            ]
        ]);
    }
}