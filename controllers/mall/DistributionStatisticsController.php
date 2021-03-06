<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 数据统计-数据统计-分销排行
 * Author: zal
 * Date: 2020-04-15
 * Time: 15:50
 */

namespace app\controllers\mall;

use app\forms\mall\statistics\DistributionForm;

class DistributionStatisticsController extends StatisticsMangerController
{
    public function actionIndex()
    {
        if (\Yii::$app->request->isAjax) {
            $form = new DistributionForm();
            $form->attributes = \Yii::$app->request->get();
            $form->attributes = \Yii::$app->request->post();
            return $this->asJson($form->search());
        } else {
            if (\Yii::$app->request->post('flag') === 'EXPORT') {
                $form = new DistributionForm();
                $form->attributes = \Yii::$app->request->post();
                $form->search();
                return false;
            } else {
                return $this->render('index');
            }
        }
    }
}