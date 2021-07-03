<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 店铺管理-内容管理-专题分类
 * Author: zal
 * Date: 2020-04-14
 * Time: 14:50
 */

namespace app\controllers\mall;

use app\forms\mall\shop\TopicTypeForm;

class TopicTypeController extends ShopManagerController
{
    public function actionIndex()
    {
        if (\Yii::$app->request->isAjax) {
            $form = new TopicTypeForm();
            $form->attributes = \Yii::$app->request->get();
            $form->attributes = json_decode(\Yii::$app->request->get('search'), true);
            return $this->asJson($form->getList());
        } else {
            return $this->render('index');
        }
    }

    public function actionDestroy()
    {
        if ($id = \Yii::$app->request->post('id')) {
            $form = new TopicTypeForm();
            $form->id = $id;
            return $this->asJson($form->destroy());
        }
    }

    public function actionSwitchStatus()
    {
        if(\Yii::$app->request->isPost) {
            $form = new TopicTypeForm();
            $form->attributes = \Yii::$app->request->post();
            return $this->asJson($form->switchStatus());
        }
    }

    public function actionEdit()
    {
        if (\Yii::$app->request->isAjax) {
            $form = new TopicTypeForm();
            if (\Yii::$app->request->isPost) {
                $form->attributes = \Yii::$app->request->post();
                return $form->save();
            } else {
                $form->attributes = \Yii::$app->request->get();
                return $this->asJson($form->getDetail());
            }
        } else {
            return $this->render('edit');
        }
    }
}
