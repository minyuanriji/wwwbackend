<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 数据统计
 * Author: zal
 * Date: 2020-04-15
 * Time: 15:50
 */

namespace app\controllers\mall;

use app\forms\mall\statistics\DataForm;
use app\forms\mall\statistics\GoodsStatisticsForm;
use app\forms\mall\statistics\InitDataForm;
use yii\base\BaseObject;

class DataStatisticsController extends StatisticsMangerController
{
    /**
     * 数据概况
     * @return string|\yii\web\Response
     */
    public function actionIndex()
    {
        if (\Yii::$app->request->isAjax) {
            $form = new DataForm();
            $form->attributes = \Yii::$app->request->get();
            $form->attributes = \Yii::$app->request->post();
            return $this->asJson($form->search());
        } else {
            return $this->render('index');
        }
    }

    /**
     * 店铺列表
     * @return \yii\web\Response
     */
    public function actionMch_list()
    {
        $form = new DataForm();
        $form->attributes = \Yii::$app->request->get();
        $form->attributes = \Yii::$app->request->post();
        return $this->asJson($form->mch_search());
    }

    /**
     * 图表查询
     * @return \yii\web\Response
     */
    public function actionTable()
    {
        $form = new DataForm();
        $form->attributes = \Yii::$app->request->get();
        $form->attributes = \Yii::$app->request->post();
        return $this->asJson($form->table_search());
    }

    /**
     * 商品查询-排序
     * @return bool|\yii\web\Response
     */
    public function actionGoods_top()
    {
        if (\Yii::$app->request->post('flag') === 'EXPORT') {
            $form = new DataForm();
            $form->attributes = \Yii::$app->request->post();
            $form->search(1);
            return false;
        } else {
            $form = new DataForm();
            $form->attributes = \Yii::$app->request->get();
            $form->attributes = \Yii::$app->request->post();
            return $this->asJson($form->search(1));
        }
    }

    /**
     * 用户查询-排序
     * @return bool|\yii\web\Response
     */
    public function actionUsers_top()
    {
        if (\Yii::$app->request->post('flag') === 'EXPORT') {
            $form = new DataForm();
            $form->attributes = \Yii::$app->request->post();
            $form->search(2);
            return false;
        } else {
            $form = new DataForm();
            $form->attributes = \Yii::$app->request->get();
            $form->attributes = \Yii::$app->request->post();
            return $this->asJson($form->search(2));
        }
    }

    /**
     * 数据初始
     * @return \yii\web\Response
     */
    public function actionInitial()
    {
        $form = new InitDataForm();
        return $this->asJson($form->search());
    }

    /**
     * 商品统计
     * @return string|\yii\web\Response
     */
    public function actionGoodsStatistics()
    {
        if (\Yii::$app->request->isAjax) {
            $form = new GoodsStatisticsForm();
            $form->attributes = \Yii::$app->request->get();
            return $this->asJson($form->search());
        } else {
            return $this->render('goods-statistics');
        }
    }
}
