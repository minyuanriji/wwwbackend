<?php

namespace app\plugins\addcredit\controllers\mall\plateforms;

use app\plugins\addcredit\forms\mall\plateforms\PlateformsEditForm;
use app\plugins\addcredit\forms\mall\plateforms\PlateformsForm;
use app\plugins\Controller;

class PlateformsController extends Controller
{
    /**
     * @Note:平台列表
     * @return string|\yii\web\Response
     */
    public function actionIndex()
    {
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isPost) {
            } else {
                $form = new PlateformsForm();
                $form->attributes = \Yii::$app->request->get();
                $list = $form->search();
                return $this->asJson($list);
            }
        } else {
            return $this->render('index');
        }
    }

    /**
     * @Note:编辑平台
     * @return string|\yii\web\Response
     */
    public function actionEdit()
    {
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isPost) {
                $form = new PlateformsEditForm();
                $form->attributes = \Yii::$app->request->post('form');
                $res = $form->save();

                return $this->asJson($res);
            } else {
                $form = new PlateformsForm();
                $form->attributes = \Yii::$app->request->get();
                $detail = $form->getDetail();

                return $this->asJson($detail);
            }
        } else {
            return $this->render('edit');
        }
    }

    /**
     * @Note:删除平台---弃用
     * @return \yii\web\Response
     */
    public function actionDelete()
    {
        $form = new PlateformsForm();
        $form->attributes = \Yii::$app->request->post();
        $res = $form->delete();

        return $this->asJson($res);
    }

    /**
     * @Note:启用或关闭平台
     * @return \yii\web\Response
     */
    public function actionIsEnable()
    {
        $form = new PlateformsForm();
        $form->attributes = \Yii::$app->request->post();
        return $this->asJson($form->isEnable());
    }

    /**
     * @Note:删除平台产品
     * @return \yii\web\Response
     */
    public function actionDelProduct()
    {
        $form = new PlateformsForm();
        $form->attributes = \Yii::$app->request->post();
        return $this->asJson($form->delProduct());
    }

    /**
     * @Note:添加平台产品
     * @return \yii\web\Response
     */
    public function actionAddProduct()
    {
        $form = new PlateformsForm();
        $form->attributes = \Yii::$app->request->post();
        return $this->asJson($form->addProduct());
    }

}