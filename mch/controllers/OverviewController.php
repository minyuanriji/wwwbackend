<?php
namespace app\mch\controllers;

use app\mch\forms\statistics\DataForm;
use app\mch\forms\statistics\InitDataForm;

class OverviewController extends MchController{
    /**
     * 数据概况
     * @return string|\yii\web\Response
     */
    public function actionIndex(){
        if (\Yii::$app->request->isAjax) {
            $form = new DataForm();
            $form->attributes = \Yii::$app->request->get();
            $form->attributes = \Yii::$app->request->post();
            $form->mch_id     = \Yii::$app->mchAdmin->identity->mchModel->id;
            return $this->asJson($form->search());
        } else {
            return $this->render('index');
        }
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
        $form->mch_id     = \Yii::$app->mchAdmin->identity->mchModel->id;
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
            $form->mch_id     = \Yii::$app->mchAdmin->identity->mchModel->id;
            $form->search(1);
            return false;
        } else {
            $form = new DataForm();
            $form->attributes = \Yii::$app->request->get();
            $form->attributes = \Yii::$app->request->post();
            $form->mch_id     = \Yii::$app->mchAdmin->identity->mchModel->id;
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
            $form->mch_id     = \Yii::$app->mchAdmin->identity->mchModel->id;
            $form->search(2);
            return false;
        } else {
            $form = new DataForm();
            $form->attributes = \Yii::$app->request->get();
            $form->attributes = \Yii::$app->request->post();
            $form->mch_id     = \Yii::$app->mchAdmin->identity->mchModel->id;
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
}
