<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * Created by PhpStorm
 * Author: zal
 * Date: 2020-04-08
 * Time: 14:12
 */

namespace app\controllers\admin;


use app\core\ApiCode;
use app\forms\admin\AdminForm;
use app\forms\admin\LoginForm;
use app\forms\mall\mch\MchSettingForm;
use app\models\User;

class AdminController extends BaseController
{
    public $layout = 'main';

    /**
     * @Author: zal
     * @Date: 2020-04-08
     * @Time: 15:16
     * @Note: 登录
     * @return $this
     */
    public function actionLogin()
    {
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isPost) {
                $form = new LoginForm();
                $form->attributes = \Yii::$app->request->post('form');
                //var_dump($form->attributes);exit;
                $form->mall_id = \Yii::$app->request->post('mall_id');
                $res = $form->login();
                return $this->asJson($res);
            }
        } else {
            return $this->render('login');
        }
    }

    /**
     * @Author: zal
     * @Date: 2020-04-08
     * @Time: 15:16
     * @Note: 注销
     * @return \yii\web\Response
     */
    public function actionLogout()
    {
        $logout = \Yii::$app->admin->logout();

        return $this->asJson([
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '请求成功',
            'data' => [
                'url' => 'admin/admin/login'
            ]
        ]);
    }

    /**
     * 多商户登录
     * @return string|\yii\web\Response
     */
    public function actionMchLogin()
    {
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isPost) {
                $form = new MchPassportForm();
                $form->attributes = \Yii::$app->request->post('form');
                $res = $form->login();

                return $this->asJson($res);
            }
        } else {
            return $this->render('mch-login');
        }
    }

    public function actionMchSetting()
    {
        if (\Yii::$app->request->isAjax) {
            $form = new MchSettingForm();
            $form->mall_id = \Yii::$app->request->get('mall_id');
            $res = $form->getMchSetting();

            return $this->asJson($res);
        }
    }
}
