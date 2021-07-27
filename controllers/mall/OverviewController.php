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


use app\forms\mall\data_statistics\OverviewForm;

class OverviewController extends StatisticsMangerController
{
    /**
     * 数据概况
     * @return string|\yii\web\Response
     */
    public function actionIndex()
    {
        if (\Yii::$app->request->isAjax) {
            $form = new OverviewForm();
            $form->attributes = \Yii::$app->request->post();
            return $this->asJson($form->search());
        } else {
            return $this->render('index');
        }
    }
}
