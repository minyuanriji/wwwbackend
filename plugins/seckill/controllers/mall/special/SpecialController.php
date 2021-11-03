<?php

namespace app\plugins\seckill\controllers\mall\special;

use app\plugins\Controller;
use app\plugins\seckill\forms\mall\special\SpecialDestroyForm;
use app\plugins\seckill\forms\mall\special\SpecialDetailsForm;
use app\plugins\seckill\forms\mall\special\SpecialEditForm;
use app\plugins\seckill\forms\mall\special\SpecialListForm;

class SpecialController extends Controller
{
    /**
     * @Note:秒杀专题列表
     * @return string|\yii\web\Response
     */
    public function actionIndex()
    {
        if (\Yii::$app->request->isAjax) {
            $form = new SpecialListForm();
            $form->attributes = \Yii::$app->request->get();
            return $this->asJson($form->search());
        } else {
            return $this->render('index');
        }
    }

    /**
     * @Note:编辑秒杀专题
     * @return string|\yii\web\Response
     */
    public function actionEdit()
    {
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isPost) {
                $form = new SpecialEditForm();
                $form->attributes = \Yii::$app->request->post('form');
                $res = $form->save();

                return $this->asJson($res);
            } else {
                $form = new SpecialDetailsForm();
                $form->attributes = \Yii::$app->request->get();
                $detail = $form->getDetail();

                return $this->asJson($detail);
            }
        } else {
            return $this->render('edit');
        }
    }

    /**
     * @Note:删除秒杀专题
     * @return \yii\web\Response
     */
    public function actionDestroy()
    {
        $form = new SpecialDestroyForm();
        $form->attributes = \Yii::$app->request->post();
        $res = $form->delete();

        return $this->asJson($res);
    }

}