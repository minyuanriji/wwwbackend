<?php

namespace app\plugins\perform_distribution\controllers\mall;

use app\plugins\Controller;
use app\plugins\perform_distribution\forms\mall\AwardListForm;

class AwardController extends Controller{

    /**
     * 奖励明细
     * @return string|yii\web\Response
     */
    public function actionOrder(){
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isPost) {

            } else {
                $form = new AwardListForm();
                $form->attributes = \Yii::$app->request->get();
                return $this->asJson($form->getList());
            }
        } else {
            return $this->render('index');
        }
    }

}