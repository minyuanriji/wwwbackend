<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * Created by PhpStorm
 * Author: ganxiaohao
 * Date: 2020-04-17
 * Time: 15:52
 */

namespace app\controllers\mall;


use app\forms\mall\printer\PrinterEditForm;
use app\forms\mall\printer\PrinterForm;
use app\forms\mall\printer\PrinterSettingEditForm;
use app\forms\mall\printer\PrinterSettingForm;

/**
 * Class PrinterController
 * @package app\controllers\mall
 * @Notes打印机类
 */

class PrinterController extends MallController
{

    public function actionIndex()
    {
        if (\Yii::$app->request->isAjax) {
            $form = new PrinterForm();
            $form->attributes = \Yii::$app->request->get();
            return $this->asJson($form->search());
        } else {
            return $this->render('index');
        }
    }

    public function actionDelete()
    {
        if ($id = \Yii::$app->request->post('id')) {
            $form = new PrinterForm();
            $form->id = $id;
            return $this->asJson($form->delete());
        }
    }

    public function actionEdit()
    {
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isPost) {
                $form = new PrinterEditForm();
                $form->attributes = \Yii::$app->request->post();
                return $form->save();
            } else {
                $form = new PrinterForm();
                $form->attributes = \Yii::$app->request->get();
                return $this->asJson($form->getDetail());
            }
        } else {
            return $this->render('edit');
        }
    }

    public function actionSetting()
    {
        if (\Yii::$app->request->isAjax) {
            $form = new PrinterSettingForm();
            $form->attributes = \Yii::$app->request->get();
            return $this->asJson($form->search());
        } else {
            return $this->render('setting');
        }
    }

    public function actionSettingDelete()
    {
        if ($id = \Yii::$app->request->post('id')) {
            $form = new PrinterSettingForm();
            $form->id = $id;
            return $this->asJson($form->delete());
        }
    }

    public function actionSettingEdit()
    {
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isPost) {
                $form = new PrinterSettingEditForm();
                $form->attributes = \Yii::$app->request->post();
                return $form->save();
            } else {
                $form = new PrinterSettingForm();
                $form->attributes = \Yii::$app->request->get();
                return $this->asJson($form->getDetail());
            }
        } else {
            return $this->render('setting');
        }
    }



}