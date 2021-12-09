<?php

namespace app\plugins\mch\controllers\mall;

use app\plugins\Controller;
use app\plugins\mch\forms\mall\GoodsForm;
use app\plugins\mch\forms\mall\GoodsListForm;
use app\forms\mall\goods\GoodsEditForm;

class GoodsController extends Controller
{
    public function actionIndex()
    {
        if (\Yii::$app->request->isAjax) {
            $form = new GoodsListForm();
            $form->attributes = \Yii::$app->request->get();
            $form->attributes = \Yii::$app->request->get('search');
            $res = $form->getList();

            return $this->asJson($res);
        } else {
            return $this->render('index');
        }
    }

    public function actionEdit()
    {
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isPost) {
                $data = \Yii::$app->request->post();

                $form = new GoodsEditForm();

                $data_form = json_decode($data['form'], true);

                $form->attributes = $data_form;

                $form->attrGroups = json_decode($data['attrGroups'], true);
                $form -> expressName = json_decode($data['expressName'],true);

                $res = $form->save();
                return $this->asJson($res);
            } else {
                $form = new GoodsForm();
                $form->attributes = \Yii::$app->request->get();
                $res = $form->getDetail();

                return $this->asJson($res);
            }
        } else {
            return $this->render('edit');
        }
    }

    public function actionSwitchStatus()
    {
        $form = new GoodsForm();
        $form->attributes = \Yii::$app->request->post();
        $res = $form->switchStatus();

        return $this->asJson($res);
    }

    public function actionBatchSwitchStatus()
    {
        $form = new GoodsForm();
        $form->attributes = \Yii::$app->request->post();
        $res = $form->batchSwitchStatus();

        return $this->asJson($res);
    }

    public function actionAuditSubmit()
    {
        $form = new GoodsForm();
        $form->attributes = \Yii::$app->request->post('form');
        $res = $form->auditSubmit();

        return $this->asJson($res);
    }

    public function actionCats()
    {
        $form = new GoodsForm();
        $form->attributes = \Yii::$app->request->get();
        $res = $form->getCatList();

        return $this->asJson($res);
    }

    public function actionEditSort()
    {
        $form = new GoodsForm();
        $form->attributes = \Yii::$app->request->post();
        $res = $form->editSort();

        return $this->asJson($res);
    }
}
