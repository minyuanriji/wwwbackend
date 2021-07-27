<?php
/**
 * Created by PhpStorm.
 * User: 阿源
 * Date: 2020/10/21
 * Time: 18:04
 */

namespace app\controllers\mall;

use app\forms\mall\data_statistics\StatisticsForm;

class StatisticsController extends ShopManagerController
{
    public function actionIndex()
    {
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isGet) {
                $form = new StatisticsForm();
                return $this->asJson($form->search());
            }
            if (\Yii::$app->request->isPost){
                $form = new StatisticsForm();
                $post = \Yii::$app->request->post();
                $form->attributes = $post;
                return $this->asJson($form->save());
            }
        } else {
            return $this->render('index');
        }
    }


    //获取省份
    public function actionGetProvince(){
        if (\Yii::$app->request->isAjax) {

            if (\Yii::$app->request->isPost) {
                $form = new StatisticsForm();
                return $this->asJson($form->getProvince());
            }
        }
    }
}