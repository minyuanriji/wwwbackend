<?php

namespace app\plugins\perform_distribution\controllers\mall;

use app\plugins\Controller;
use app\plugins\perform_distribution\forms\mall\RegionDeleteForm;
use app\plugins\perform_distribution\forms\mall\RegionDetailForm;
use app\plugins\perform_distribution\forms\mall\RegionEditForm;
use app\plugins\perform_distribution\forms\mall\RegionListForm;

class RegionController extends Controller{

    /**
     * 获取区域数据
     * @return string|yii\web\Response
     */
    public function actionIndex(){
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isPost) {

            } else {
                $form = new RegionListForm();
                $form->attributes = \Yii::$app->request->get();
                return $this->asJson($form->getList());
            }
        } else {
            return $this->render('index');
        }
    }

    /**
     * @Note:编辑区域
     * @return string|\yii\web\Response
     */
    public function actionEdit(){
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isPost) {
                $form = new RegionEditForm();
                $form->attributes = \Yii::$app->request->post();
                return $this->asJson($form->save());
            } elseif (\Yii::$app->request->isGet) {
                $form = new RegionDetailForm();
                $form->attributes = \Yii::$app->request->get();
                return $this->asJson($form->getDetail());
            }
        }
        return $this->render('edit');
    }

    /**
     * 删除
     * @return \yii\web\Response
     */
    public function actionDelete(){
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isPost) {
                $form = new RegionDeleteForm();
                $form->attributes = \Yii::$app->request->post();
                return $this->asJson($form->save());
            }
        }
    }
}