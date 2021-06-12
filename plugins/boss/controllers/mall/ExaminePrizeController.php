<?php

namespace app\plugins\boss\controllers\mall;

use app\plugins\boss\forms\mall\BossAwardsExamineListForm;
use app\plugins\Controller;

class ExaminePrizeController extends Controller
{
    /**
     * @Note:审核列表
     * @return string|\yii\web\Response
     */
    public function actionIndex()
    {
        if (\Yii::$app->request->isAjax) {
            $form = new BossAwardsExamineListForm();
            $form->attributes = \Yii::$app->request->get();
            return $this->asJson($form->search());
        }
        return $this->render('index');
    }

    /**
     * @Note:审核
     * @return string|\yii\web\Response
     */
    public function actionExamine()
    {
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isGet) {
                $form = new BossAwardsExamineListForm();
                $id = \Yii::$app->request->get('id');
                return $this->asJson($form->examine($id));
            }
        }
        return $this->render('edit');
    }

}