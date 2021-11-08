<?php

namespace app\plugins\oil\controllers\mall;

use app\plugins\Controller;
use app\plugins\oil\forms\mall\OilPlateformAddProductForm;
use app\plugins\oil\forms\mall\OilPlateformDelProductForm;
use app\plugins\oil\forms\mall\OilPlateformDetailForm;
use app\plugins\oil\forms\mall\OilPlateformEditForm;
use app\plugins\oil\forms\mall\OilPlateformListForm;
use app\plugins\oil\forms\mall\OilPlateformSwitchEnabledForm;

class PlateformController extends Controller{

    /**
     * 平台列表
     * @return string|\yii\web\Response
     */
    public function actionList(){
        if (\Yii::$app->request->isAjax) {
            $form = new OilPlateformListForm();
            $form->attributes = \Yii::$app->request->get();
            return $this->asJson($form->getList());
        } else {
            return $this->render('index');
        }
    }

    /**
     * 切换使用状态
     * @return string|\yii\web\Response
     */
    public function actionSwitchEnabled(){
        $form = new OilPlateformSwitchEnabledForm();
        $form->attributes = \Yii::$app->request->post();
        return $this->asJson($form->update());
    }

    /**
     * @Note:编辑平台
     * @return string|\yii\web\Response
     */
    public function actionEdit(){
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isPost) {
                $form = new OilPlateformEditForm();
                $form->attributes = \Yii::$app->request->post();
                return $this->asJson($form->save());
            } else {
                $form = new OilPlateformDetailForm();
                $form->attributes = \Yii::$app->request->get();
                return $this->asJson($form->getDetail());
            }
        } else {
            return $this->render('edit');
        }
    }

    /**
     * @Note:添加平台产品
     * @return \yii\web\Response
     */
    public function actionAddProduct(){
        $form = new OilPlateformAddProductForm();
        $form->attributes = \Yii::$app->request->post();
        return $this->asJson($form->addProduct());
    }

    /**
     * @Note:删除平台产品
     * @return \yii\web\Response
     */
    public function actionDelProduct(){
        $form = new OilPlateformDelProductForm();
        $form->attributes = \Yii::$app->request->post();
        return $this->asJson($form->delProduct());
    }
}