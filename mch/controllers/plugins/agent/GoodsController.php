<?php
namespace app\mch\controllers\plugins\agent;


use app\mch\controllers\MchController;
use app\plugins\agent\forms\mall\AgentGoodsForm;

class GoodsController extends MchController {

    public function actionGoodsSetting(){
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isPost) {
                $form = new AgentGoodsForm();
                $form->attributes = \Yii::$app->request->post()['form'];
                return $this->asJson($form->saveAgentGoodsSetting());
            } else {
                $form = new AgentGoodsForm();
                $form->attributes = \Yii::$app->request->get();
                return $this->asJson($form->getAgentGoodsSetting());
            }
        } else {
            return $this->render('edit');
        }
    }

}