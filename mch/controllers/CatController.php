<?php
namespace app\mch\controllers;

use app\mch\forms\cat\CatEditForm;
use app\mch\forms\cat\CatForm;
use app\mch\forms\cat\CatStyleForm;

class CatController extends MchController{

    public function actionIndex(){
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isPost) {
            } else {
                $form = new CatForm();
                $form->attributes = \Yii::$app->request->get();
                $list = $form->getList();
                return $this->asJson($list);
            }
        } else {
            return $this->render('index');
        }
    }

    /**
     * 删除
     * @return \yii\web\Response
     */
    public function actionDelete(){
        $form = new CatForm();
        $form->attributes = \Yii::$app->request->post();
        $res = $form->destroy();

        return $this->asJson($res);
    }

    public function actionStyle(){
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isPost) {
                $form = new CatStyleForm();
                $form->attributes = \Yii::$app->request->post();
                return $form->save();
            } else {
                $form = new CatStyleForm();
                $form->attributes = \Yii::$app->request->get();
                return $this->asJson($form->search());
            }
        } else {
            return $this->render('style');
        }
    }

    public function actionSwitchStatus(){
        $form = new CatForm();
        $form->attributes = \Yii::$app->request->get();
        $res = $form->switchStatus();

        return $this->asJson($res);
    }

    public function actionSort() {
        $form = new CatForm();
        $form->attributes = \Yii::$app->request->post();
        return $this->asJson($form->storeSort());
    }

    public function actionTransferCat(){
        $form = new CatForm();
        $form->attributes = \Yii::$app->request->get();
        return $this->asJson($form->transferCat());
    }

    public function actionStoreSort(){
        $form = new CatForm();
        $form->attributes = \Yii::$app->request->post();
        return $this->asJson($form->storeSort());
    }

    public function actionAllList(){
        $form = new CatForm();
        $form->attributes = \Yii::$app->request->get();
        $res = $form->getAllList();
        return $res;
    }

    /**
     * 获取商品分类列表
     * @return \yii\web\Response
     */
    public function actionOptions(){
        $form = new CatForm();
        $form->attributes = \Yii::$app->request->get();
        $res = $form->getOptionList();

        return $this->asJson($res);
    }

    /**
     * 添加、编辑
     * @return string|\yii\web\Response
     */
    public function actionEdit(){
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isPost) {
                $form = new CatEditForm();
                $form->attributes = \Yii::$app->request->post('form');
                $res = $form->save();
                return $this->asJson($res);
            } else {
                $form = new CatForm();
                $form->attributes = \Yii::$app->request->get();
                $detail = $form->getDetail();

                return $this->asJson($detail);
            }
        } else {
            return $this->render('edit');
        }
    }
}