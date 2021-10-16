<?php

namespace app\controllers\mall;

use app\forms\mall\commission\AddcreditRecommendLogListForm;
use app\forms\mall\commission\AddcreditScanCodeLogListForm;
use app\forms\mall\commission\GoodsConsumeLogListForm;
use app\forms\mall\commission\HotelRecommendLogListForm;
use app\forms\mall\commission\HotelScanCodeLogListForm;
use app\forms\mall\commission\StoreScanCodeLogListForm;
use app\forms\mall\commission\StoreRecommendLogListForm;

class CommissionController extends MallController
{
    /**
     * @Note:商品消费分佣记录
     * @return string|\yii\web\Response
     */
    public function actionGoodsConsumeLog()
    {
        if (\Yii::$app->request->isAjax) {
            $form = new GoodsConsumeLogListForm();
            $form->attributes = \Yii::$app->request->get();
            return $this->asJson($form->getList());
        } else {
            return $this->render('goods-consume-log');
        }
    }

    /**
     * @Note:门店推荐分佣记录
     * @return string|\yii\web\Response
     */
    public function actionStoreRecommendLog()
    {
        if (\Yii::$app->request->isAjax) {
            $form = new StoreRecommendLogListForm();
            $form->attributes = \Yii::$app->request->get();
            return $this->asJson($form->getList());
        } else {
            return $this->render('store-recommend-log');
        }
    }

    /**
     * @Note:门店结账分佣记录
     * @return string|\yii\web\Response
     */
    public function actionStoreScanCodeLog()
    {
        if (\Yii::$app->request->isAjax) {
            $form = new StoreScanCodeLogListForm();
            $form->attributes = \Yii::$app->request->get();
            return $this->asJson($form->getList());
        } else {
            return $this->render('store-scan-code-log');
        }
    }

    /**
     * @Note:酒店推荐分佣记录
     * @return string|\yii\web\Response
     */
    public function actionHotelRecommendLog()
    {
        if (\Yii::$app->request->isAjax) {
            $form = new HotelRecommendLogListForm();
            $form->attributes = \Yii::$app->request->get();
            return $this->asJson($form->getList());
        } else {
            return $this->render('hotel-recommend-log');
        }
    }

    /**
     * @Note:酒店消费分佣记录
     * @return string|\yii\web\Response
     */
    public function actionHotelScanCodeLog()
    {
        if (\Yii::$app->request->isAjax) {
            $form = new HotelScanCodeLogListForm();
            $form->attributes = \Yii::$app->request->get();
            return $this->asJson($form->getList());
        } else {
            return $this->render('hotel-scan-code-log');
        }
    }

    /**
     * @Note:话费推荐分佣记录
     * @return string|\yii\web\Response
     */
    public function actionAddcreditRecommendLog()
    {
        if (\Yii::$app->request->isAjax) {
            $form = new AddcreditRecommendLogListForm();
            $form->attributes = \Yii::$app->request->get();
            return $this->asJson($form->getList());
        } else {
            return $this->render('addcredit-recommend-log');
        }
    }

    /**
     * @Note:话费充值分佣记录
     * @return string|\yii\web\Response
     */
    public function actionAddcreditScanCodeLog()
    {
        if (\Yii::$app->request->isAjax) {
            $form = new AddcreditScanCodeLogListForm();
            $form->attributes = \Yii::$app->request->get();
            return $this->asJson($form->getList());
        } else {
            return $this->render('addcredit-scan-code-log');
        }
    }
}