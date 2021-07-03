<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 订单管理-评价管理
 * Author: zal
 * Date: 2020-04-17
 * Time: 14:11
 */

namespace app\controllers\mall;

use app\forms\mall\order\OrderCommentsForm;
use app\forms\mall\order\OrderCommentsEditForm;
use app\forms\mall\order_comments\OrderCommentsReplyForm;

class OrderCommentsController extends OrderManagerController
{
    public $enableCsrfValidation = false;

    /**
     * 首页
     * @return string|\yii\web\Response
     */
    public function actionIndex()
    {
        if (\Yii::$app->request->isAjax) {
            $form = new OrderCommentsForm();
            $form->attributes = \Yii::$app->request->get();
            return $this->asJson($form->search());
        } else {
            return $this->render('index');
        }
    }

    /**
     * 编辑
     * @return array|string|\yii\web\Response
     * @throws \Exception
     */
    public function actionEdit()
    {
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isPost) {
                $form = new OrderCommentsEditForm();
                $form->attributes = \Yii::$app->request->post();
                return $form->save();
            } else {
                $form = new OrderCommentsForm();
                $form->attributes = \Yii::$app->request->get();
                return $this->asJson($form->getDetail());
            }
        } else {
            return $this->render('edit');
        }
    }

    /**
     * 回复
     * @return array|string|\yii\web\Response
     */
    public function actionReply()
    {
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isPost) {
                $form = new OrderCommentsReplyForm();
                $form->attributes = \Yii::$app->request->post();
                return $form->save();
            } else {
                $form = new OrderCommentsReplyForm();
                $form->attributes = \Yii::$app->request->get();
                return $this->asJson($form->getDetail());
            }
        } else {
            return $this->render('reply');
        }
    }

    /**
     * 商品搜索
     * @return \yii\web\Response
     */
    public function actionGoodsSearch()
    {
        if (\Yii::$app->request->isAjax) {
            $form = new OrderCommentsForm();
            $form->attributes = \Yii::$app->request->get();
            return $this->asJson($form->goodsSearch());
        }
    }

    /**
     * 删除
     * @return \yii\web\Response
     */
    public function actionDestroy()
    {
        if ($id = \Yii::$app->request->post('id')) {
            $form = new OrderCommentsForm();
            $form->id = $id;
            return $this->asJson($form->destroy());
        }
    }

    /**
     * 显示
     * @return \yii\web\Response
     */
    public function actionShow()
    {
        if ($id = \Yii::$app->request->post('id')) {
            $form = new OrderCommentsForm();
            $form->attributes = \Yii::$app->request->post();
            return $this->asJson($form->show());
        }
    }

    /**
     * 批量回复
     * @return \yii\web\Response
     */
    public function actionBatchReply()
    {
        if (\Yii::$app->request->isAjax) {
            $form = new OrderCommentsForm();
            $form->attributes = \Yii::$app->request->post();
            return $this->asJson($form->batchReply());
        }
    }

    /**
     * 批量删除
     * @return \yii\web\Response
     */
    public function actionBatchDestroy()
    {
        if (\Yii::$app->request->isAjax) {
            $form = new OrderCommentsForm();
            $form->attributes = \Yii::$app->request->post();
            return $this->asJson($form->batchDestroy());
        }
    }

    /**
     * 批量更新订单状态
     * @return \yii\web\Response
     */
    public function actionBatchUpdateStatus()
    {
        if (\Yii::$app->request->isAjax) {
            $form = new OrderCommentsForm();
            $form->attributes = \Yii::$app->request->post();
            return $this->asJson($form->batchUpdateStatus());
        }
    }

    /**
     * 置顶
     * @return \yii\web\Response
     */
    public function actionUpdateTop() {
        if (\Yii::$app->request->isAjax) {
            $form = new OrderCommentsForm();
            $form->attributes = \Yii::$app->request->post();
            return $this->asJson($form->updateTop());
        }
    }
}
