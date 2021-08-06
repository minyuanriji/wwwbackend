<?php

//积分发放

namespace app\controllers\mall;

use app\forms\mall\integral\IntegralSendForm;
use yii\base\BaseObject;

class IntegralController extends MallController
{
    public function actionIndex()
    {
        if (\Yii::$app->request->isAjax) {
            $form = new IntegralSendForm();
            $form->attributes = \Yii::$app->request->get();
            return $this->asJson($form->getList());
        } else {
            return $this->render('index');
        }
    }

    public function actionDestroy()
    {
        if ($id = \Yii::$app->request->post('id')) {
            $form = new IntegralSendForm();
            $form->id = $id;
            return $this->asJson($form->destroy());
        } else {
            return $this->asJson([
                'code' => 1,
                'msg' => 'no post'
            ]);
        }
    }

    public function actionEdit()
    {
        if (\Yii::$app->request->isAjax) {
            $form = new IntegralSendForm();
            if (\Yii::$app->request->isPost) {
                $form->attributes = \Yii::$app->request->post();
                return $form->save();
            } else {
                $form->attributes = \Yii::$app->request->get();
                return $this->asJson($form->getDetail());
            }
        } else {
            return $this->render('edit');
        }
    }
}
