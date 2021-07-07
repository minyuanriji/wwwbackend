<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 营销中心管理-优惠券-优惠券管理
 * Author: zal
 * Date: 2020-04-18
 * Time: 14:50
 */

namespace app\controllers\mall;

use app\forms\mall\statistics\CouponForm;

class CouponController extends StatisticsMangerController
{
    /**
     * 首页
     * @return string|\yii\web\Response
     */
    public function actionIndex()
    {
        if (\Yii::$app->request->isAjax) {
            $form = new CouponForm();
            $form->attributes = \Yii::$app->request->get();
            return $this->asJson($form->getList());
        } else {
            return $this->render('index');
        }
    }

    /**
     * 删除
     * @return \yii\web\Response
     */
    public function actionDestroy()
    {
        if ($id = \Yii::$app->request->post('id')) {
            $form = new CouponForm();
            $form->id = $id;
            return $this->asJson($form->destroy());
        }
    }

    /**
     * 编辑
     * @return array|string|\yii\web\Response
     * @throws \yii\db\Exception
     */
    public function actionEdit()
    {
        if (\Yii::$app->request->isAjax) {
            $form = new CouponForm();
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

    /**
     * 发送
     * @return array|string|\yii\web\Response
     */
    public function actionSend()
    {
        if (\Yii::$app->request->isAjax) {
            $form = new CouponForm();
            if (\Yii::$app->request->isPost) {
                $form->attributes = \Yii::$app->request->post();
                return $form->send();
            } else {
                $form->attributes = \Yii::$app->request->get();
                return $this->asJson($form->getDetail());
            }
        } else {
            return $this->render('send');
        }
    }

    /**
     * 切换领劵中心
     * @return array
     */
    public function actionEditCenter()
    {
        if (\Yii::$app->request->isPost) {
            $form = new CouponForm();
            $form->attributes = \Yii::$app->request->post();
            return $form->editCenter();
        }
    }

    /**
     * 搜索商品
     * @return \yii\web\Response
     */
    public function actionSearchGoods()
    {
        if (\Yii::$app->request->isAjax) {
            $form = new CouponForm();
            $form->attributes = \Yii::$app->request->get();
            return $this->asJson($form->searchGoods());
        }
    }

    /**
     * 搜索用户
     * @return \yii\web\Response
     */
    public function actionSearchUser()
    {
        if (\Yii::$app->request->isAjax) {
            $form = new CouponForm();
            $form->attributes = \Yii::$app->request->get();
            return $this->asJson($form->searchUser());
        }
    }

    /**
     * 搜索分类
     * @return \yii\web\Response
     */
    public function actionSearchCat()
    {
        if (\Yii::$app->request->isAjax) {
            $form = new CouponForm();
            $form->attributes = \Yii::$app->request->get();
            return $this->asJson($form->searchCat());
        }
    }

    /**
     * 搜索优惠券
     * @return \yii\web\Response
     */
    public function actionOptions()
    {
        if (\Yii::$app->request->isAjax) {
            $form = new CouponForm();
            $form->attributes = \Yii::$app->request->get();
            return $this->asJson($form->getOptions());
        }
    }
}
