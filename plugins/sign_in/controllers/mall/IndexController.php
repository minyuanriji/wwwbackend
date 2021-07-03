<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 签到插件商城后台首页
 * Author: zal
 * Date: 2020-04-20
 * Time: 14:10
 */

namespace app\plugins\sign_in\controllers\mall;

use app\core\ApiCode;
use app\plugins\sign_in\forms\api\UserForm;
use app\plugins\sign_in\forms\mall\ConfigEditForm;
use app\plugins\sign_in\forms\mall\ConfigForm;
use app\plugins\sign_in\forms\mall\CustomizeForm;
use app\plugins\sign_in\forms\mall\TemplateForm;

class IndexController extends Controller
{
    /**
     * 首页
     * @return string|\yii\web\Response
     */
    public function actionIndex()
    {
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isGet) {
                $form = new ConfigForm();
                $form->mall = \Yii::$app->mall;
                return $this->asJson($form->search());
            }
            if (\Yii::$app->request->isPost) {
                $form = new ConfigEditForm();
                $post = \Yii::$app->request->post();
                $list = \Yii::$app->serializer->decode($post['form']);
                $form->attributes = (array)$list;
                $form->mall = \Yii::$app->mall;
                return $this->asJson($form->save());
            }
        } else {
            return $this->render('index');
        }
    }

    /**
     * 模板
     * @return string|\yii\web\Response
     */
    public function actionTemplate()
    {
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isGet) {
                $form = new TemplateForm();
                $form->mall = \Yii::$app->mall;
                $add = \Yii::$app->request->get('add');
                $platform = \Yii::$app->request->get('platform');
                return $this->asJson($form->getDetail($add, $platform));
            }
            if (\Yii::$app->request->isPost) {
                $form = new TemplateForm();
                $form->attributes = \Yii::$app->request->post();
                $form->mall = \Yii::$app->mall;
                return $this->asJson($form->save());
            }
        }
        return $this->render('template');
    }

    /**
     * 配置
     * @return string|\yii\web\Response
     */
    public function actionCustomize()
    {
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isGet) {
                $form = new CustomizeForm();
                $form->attributes = \Yii::$app->request->get();
                return $this->asJson($form->getList());
            }
            if (\Yii::$app->request->isPost) {
                $form = new CustomizeForm();
                $form->attributes = \Yii::$app->request->post();

                return $this->asJson($form->save());
            }
        }
        return $this->render('customize');
    }

    /**
     * 下拉框的优惠券列表
     * @return string|\yii\web\Response
     */
    public function actionCoupon()
    {
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isPost) {

            } elseif (\Yii::$app->request->isGet) {
                $form = new CustomizeForm();
                $form->attributes = \Yii::$app->request->get();
                return $this->asJson($form->getCouponList());
            }
        }
        return $this->render('index');
    }

    /**
     * 签到记录
     */
    public function actionRecord(){
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isPost) {
                $fields = explode(',', \Yii::$app->request->post('fields'));
                $form = new CustomizeForm();
                $form->attributes = \Yii::$app->request->post();
                $form->fields = $fields;
                return $this->asJson($form->getUserList());
            } else {
                $form = new CustomizeForm();
                $form->attributes = \Yii::$app->request->get();
                return $this->asJson($form->getUserList());
            }
        }
        return $this->render('record');
    }

    /**
     * 签到记录
     */
    public function actionUser(){
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isPost) {
                $fields = explode(',', \Yii::$app->request->post('fields'));
                $form = new UserForm();
                $form->attributes = \Yii::$app->request->post();
                $form->fields = $fields;
                return $this->asJson($form->getSignInAward());
            } else {
                $form = new UserForm();
                $form->attributes = \Yii::$app->request->get();
                return $this->asJson($form->getSignInAward());
            }
        }
        return $this->render('user');
    }

    /**
     * 规则
     * @return string|\yii\web\Response
     */
    public function actionAgreement(){

        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isPost) {
                $form = new ConfigForm();
                $form->attributes = \Yii::$app->request->post('setting');
                return $this->asJson($form->save());
            } elseif (\Yii::$app->request->isGet) {
                $form = new ConfigForm();
                $setting = $form->getOne();
                return $this->asJson([
                    'code' => ApiCode::CODE_SUCCESS,
                    'data' => [
                        'rule' => $setting,
                    ],
                ]);
            }
        }
        return $this->render('agreement');
    }

    public function actionLevel()
    {
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isPost) {
            } else {
                $form = new CustomizeForm();
                $form->attributes = \Yii::$app->request->get();
                $list = $form->getLevelList();

                return $this->asJson($list);
            }
        } else {
            return $this->render('index');
        }
    }
}

