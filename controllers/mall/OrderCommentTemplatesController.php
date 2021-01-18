<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 订单管理-订单评论模板
 * Author: zal
 * Date: 2020-04-17
 * Time: 14:11
 */

namespace app\controllers\mall;

use app\forms\mall\order\OrderCommentTemplateEditForm;
use app\forms\mall\order\OrderCommentTemplatesForm;

class OrderCommentTemplatesController extends OrderManagerController
{
    public function actionIndex()
    {
        if (\Yii::$app->request->isAjax) {
            $form = new OrderCommentTemplatesForm();
            $form->attributes = \Yii::$app->request->get();
            return $this->asJson($form->search());
        } else {
            return $this->render('index');
        }
    }

    public function actionEdit()
    {
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isPost) {
                $form = new OrderCommentTemplateEditForm();
                $form->attributes = \Yii::$app->request->post();
                return $form->save();
            } else {
                $form = new OrderCommentTemplatesForm();
                $form->attributes = \Yii::$app->request->get();
                return $this->asJson($form->getDetail());
            }
        } else {
            return $this->render('edit');
        }
    }

    public function actionDestroy()
    {
        if ($id = \Yii::$app->request->post('id')) {
            $form = new OrderCommentTemplatesForm();
            $form->id = $id;
            return $this->asJson($form->destroy());
        }
    }
}
