<?php

namespace app\plugins\boss\controllers\mall;

use app\plugins\boss\forms\mall\BossAwardsBatchExamineListForm;
use app\plugins\boss\forms\mall\BossAwardsExamineDeleteForm;
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

    /**
     * @Note:批量审核
     * @return string|\yii\web\Response
     */
    public function actionBatchExamine()
    {
        if (\Yii::$app->request->isPost) {
            $form = new BossAwardsBatchExamineListForm();
            $form->ids = \Yii::$app->request->post('ids');
            return $this->asJson($form->examine());
        }
    }

    /**
     * @Note:审核列表
     * @return string|\yii\web\Response
     */
    public function actionDoDelete()
    {
        if (\Yii::$app->request->isAjax) {
            $form = new BossAwardsExamineDeleteForm();
            $form->attributes = \Yii::$app->request->post();
            return $this->asJson($form->doDelete());
        }
    }

}