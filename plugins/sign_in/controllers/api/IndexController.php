<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 签到插件首页接口类
 * Author: zal
 * Date: 2020-04-20
 * Time: 14:10
 */

namespace app\plugins\sign_in\controllers\api;

use app\controllers\api\filters\LoginFilter;
use app\plugins\sign_in\forms\api\IndexForm;
use app\plugins\sign_in\forms\api\SignInForm;
use app\plugins\sign_in\forms\api\SignInResultForm;
use app\plugins\sign_in\forms\api\UserForm;

class IndexController extends ApiController
{
    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'login' => [
                'class' => LoginFilter::class,
                'ignore' => ['index', 'customize', 'sign-in-day']
            ],
        ]);
    }

    /**
     * 首页
     * @return \yii\web\Response
     */
    public function actionIndex()
    {
        $form = new IndexForm();
        $form->attributes = $this->requestData;
        $form->mall = \Yii::$app->mall;
        $form->user = \Yii::$app->user->identity;
        return $this->asJson($form->search());
    }


    public function actionUserSignIn(){
        $form = new UserForm();
        $form->attributes = $this->requestData;
        $form->mall = \Yii::$app->mall;
        $form->user = \Yii::$app->user->identity;
        return $this->asJson($form->save());
    }

    /**
     * 签到
     * @return \yii\web\Response
     */
    public function actionSignIn()
    {
        $form = new SignInForm();
        $form->attributes = \Yii::$app->request->get();
        $form->mall = \Yii::$app->mall;
        $form->user = \Yii::$app->user->identity;
//        return $this->asJson($form->save());
    }

    /**
     * 签到结果
     * @return \yii\web\Response
     */
    public function actionSignInResult()
    {
        $form = new SignInResultForm();
        $form->attributes = \Yii::$app->request->get();
        $form->mall = \Yii::$app->mall;
        $form->user = \Yii::$app->user->identity;
        return $this->asJson($form->search());
    }

    /**
     * 签到天数
     * @return \yii\web\Response
     */
    public function actionSignInDay()
    {
        $form = new IndexForm();
        $form->attributes = \Yii::$app->request->get();
        $form->mall = \Yii::$app->mall;
        $form->user = \Yii::$app->user->identity;
        return $this->asJson($form->getDay());
    }

    /**
     * 用户签到
     * @return \yii\web\Response
     */
    public function actionUser()
    {
        $form = new UserForm();
        $form->attributes = \Yii::$app->request->get();
        $form->mall = \Yii::$app->mall;
        $form->user = \Yii::$app->user->identity;
//        return $this->asJson($form->save());
    }

    /**
     * 配置
     * @return \yii\web\Response
     */
    public function actionCustomize()
    {
        $form = new IndexForm();
        $form->attributes = \Yii::$app->request->get();
        $form->mall = \Yii::$app->mall;
        $form->user = \Yii::$app->user->identity;
        return $this->asJson($form->getCustomize());
    }
}
