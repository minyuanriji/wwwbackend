<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 数据统计-数据统计-秒杀排行
 * Author: zal
 * Date: 2020-04-24
 * Time: 15:50
 */

namespace app\controllers\mall;


class SeckillStatisticsController extends StatisticsMangerController
{
    public function actionIndex()
    {
        if (\Yii::$app->request->isAjax) {
            $plugin = \Yii::$app->plugin->getPlugin('seckill');
            $form = $plugin->getApi();
            $form->attributes = \Yii::$app->request->get();
            $form->attributes = \Yii::$app->request->post();
            return $this->asJson($form->search());
        } else {
            if (\Yii::$app->request->post('flag') === 'EXPORT') {
                $plugin = \Yii::$app->plugin->getPlugin('seckill');
                $form = $plugin->getApi();
                $form->attributes = \Yii::$app->request->post();
                $form->search();
                return false;
            } else {
                return $this->render('index');
            }
        }
    }
}