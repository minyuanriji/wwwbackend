<?php

namespace app\plugins\perform_distribution\controllers\mall;

use app\plugins\Controller;
use app\plugins\perform_distribution\forms\mall\LevelDeleteForm;
use app\plugins\perform_distribution\forms\mall\LevelDetailForm;
use app\plugins\perform_distribution\forms\mall\LevelEditForm;
use app\plugins\perform_distribution\forms\mall\LevelListForm;
use Yii;

class LevelController extends Controller{


    /**
     * 等级设置
     * @return string|yii\web\Response
     */
    public function actionIndex(){
        if (Yii::$app->request->isAjax) {
            if (Yii::$app->request->isPost) {

            } elseif (Yii::$app->request->isGet) {
                $form = new LevelListForm();
                $form->attributes = Yii::$app->request->get();
                return $this->asJson($form->getList());
            }
        }

        return $this->render('index');
    }

    /**
     * 编辑
     * @return string|yii\web\Response
     */
    public function actionEdit(){
        if (Yii::$app->request->isAjax) {
            if (Yii::$app->request->isPost) {
                $form = new LevelEditForm();
                $form->attributes = Yii::$app->request->post();
                return $this->asJson($form->save());
            } else{
                $form = new LevelDetailForm();
                $form->attributes = Yii::$app->request->get();
                return $this->asJson($form->getDetail());
            }
        }
        return $this->render('edit');
    }

    /**
     * 删除
     * @return yii\web\Response
     */
    public function actionDelete(){
        $form = new LevelDeleteForm();
        $form->attributes = Yii::$app->request->post();
        return $this->asJson($form->save());
    }

}