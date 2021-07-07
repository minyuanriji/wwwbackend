<?php
/**
  * @link:http://www.gdqijianshi.com/
 * copyright: Copyright (c) 2020 广东七件事集团
 * author: zal
 */


namespace app\plugins\mpwx\controllers\mall;


use app\core\ApiCode;
use app\plugins\Controller;
use app\plugins\mpwx\forms\subscribe\SubscribeForm;
use app\plugins\mpwx\forms\template_msg\TemplateMsgEditForm;
use app\plugins\mpwx\forms\template_msg\TemplateMsgForm;
use app\plugins\mpwx\forms\template_msg\TemplateSendForm;

class TemplateMsgController extends Controller
{
    public function actionSetting()
    {
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isGet) {
                $form = new SubscribeForm();
                $res = $form->getDetail();

                return $this->asJson($res);
            } else {
                $form = new SubscribeForm();
                $form->data = \Yii::$app->request->post('list');
                return $form->save();
            }
        } else {
            return $this->render('setting');
        }
    }

    public function actionAddTemplate()
    {
        $form = new SubscribeForm();
        $form->mall = \Yii::$app->mall;
        return $this->asJson($form->saveAll());
    }

    /**
     * 群发模板消息
     * @return array|string|\yii\web\Response
     */
    public function actionSend()
    {
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isGet) {
            } else {
                $form = new TemplateSendForm();
                $form->userId = \Yii::$app->request->post('user_id');
                $form->formData = \Yii::$app->request->post('form');
                $form->templateData = \Yii::$app->request->post('template');


                return $this->asJson($form->send());
            }
        } else {
            return $this->render('send');
        }
    }

    /**
     * 模板列表
     * @return array|string|\yii\web\Response
     */
    public function actionTemplate()
    {
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isGet) {
                $form = new TemplateMsgForm();
                $res = $form->getList();
                return [
                    'code' => ApiCode::CODE_SUCCESS,
                    'msg' => '请求成功',
                    'data' => [
                        'list' => $res,
                    ]
                ];
               // return $this->asJson($res);
            } else {
                $form = new TemplateMsgEditForm();
                $form->data = \Yii::$app->request->post('form');
                return $form->save();
            }
        } else {
            return $this->render('send');
        }
    }

    /**
     * 删除模板
     * @return array|string
     */
    public function actionDestroyTemplate()
    {
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isGet) {
            } else {
                $form = new TemplateMsgEditForm();
                $form->data = \Yii::$app->request->post('list');
                return $form->destroyTemplate();
            }
        } else {
            return $this->render('send');
        }
    }

    /**
     * 获取用户
     */
    public function actionUsers()
    {
        $form = new TemplateMsgForm();
        $form->attributes = \Yii::$app->request->get();

        if (\Yii::$app->request->get('is_all')) {
            return $form->getAllUsers();
        } else {
            $platform = \Yii::$app->request->get('platform');
            return $this->asJson($form->getUsers($platform));
        }
    }

    /**
     * 单个模板消息发送测试
     */
    public function actionTestSend()
    {
        $form = new TestUserForm();
        $form->user_id = \Yii::$app->request->post('user_id');
        $form->tpl_id = \Yii::$app->request->post('tpl_id');
        return $this->asJson($form->testSend());
    }

    public function actionAddTestUser()
    {
        $form = new TestUserForm();
        $form->user = \Yii::$app->request->post('user');

        return $this->asJson($form->addTestUser());
    }

    public function actionTestUser()
    {
        $form = new TestUser();
        return $this->asJson($form->getTestUser());
    }
}
