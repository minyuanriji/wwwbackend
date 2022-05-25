<?php

namespace app\plugins\perform_distribution\controllers\mall;

use app\plugins\Controller;
use app\plugins\perform_distribution\forms\mall\GoodsDeleteForm;
use app\plugins\perform_distribution\forms\mall\GoodsEditForm;
use app\plugins\perform_distribution\forms\mall\GoodsListForm;

class GoodsController extends Controller{

    /**
     * 获取商品数据
     * @return string|yii\web\Response
     */
    public function actionIndex(){
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isPost) {

            } else {
                $form = new GoodsListForm();
                $form->attributes = \Yii::$app->request->get();
                return $this->asJson($form->getList());
            }
        } else {
            return $this->render('index');
        }
    }

    /**
     * 编辑商品
     * @return void|\yii\web\Response
     */
    public function actionEdit(){
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isPost) {
                $form = new GoodsEditForm();
                $form->attributes = \Yii::$app->request->post();
                return $this->asJson($form->save());
            }
        }
    }

    /**
     * 删除
     * @return \yii\web\Response
     */
    public function actionDelete(){

        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isPost) {
                $form = new GoodsDeleteForm();
                $form->attributes = \Yii::$app->request->post();
                return $this->asJson($form->save());
            }
        }
    }
}