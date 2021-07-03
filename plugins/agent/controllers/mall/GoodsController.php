<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * Created by PhpStorm
 * Author: ganxiaohao
 * Date: 2020-07-08
 * Time: 14:30
 */

namespace app\plugins\agent\controllers\mall;


use app\plugins\agent\forms\mall\AgentGoodsForm;
use app\plugins\Controller;

class GoodsController extends Controller
{

    public function actionAgentSetting()
    {


        if (\Yii::$app->request->isAjax) {

            if (\Yii::$app->request->isGet) {
                $form = new AgentGoodsForm();
                return $this->asJson($form->getAgentSetting());
            }
        }
    }


    public function actionGoodsSetting()
    {

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