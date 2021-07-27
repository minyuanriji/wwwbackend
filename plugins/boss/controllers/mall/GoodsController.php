<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * Created by PhpStorm
 * Author: ganxiaohao
 * Date: 2020-07-08
 * Time: 14:30
 */

namespace app\plugins\boss\controllers\mall;


use app\plugins\boss\forms\mall\BossGoodsForm;
use app\plugins\Controller;

class GoodsController extends Controller
{

    public function actionBossSetting()
    {


        if (\Yii::$app->request->isAjax) {

            if (\Yii::$app->request->isGet) {
                $form = new BossGoodsForm();
                return $this->asJson($form->getBossSetting());
            }
        }
    }


    public function actionGoodsSetting()
    {

        if (\Yii::$app->request->isAjax) {

            if (\Yii::$app->request->isPost) {
                $form = new BossGoodsForm();
                $form->attributes = \Yii::$app->request->post()['form'];
                return $this->asJson($form->saveBossGoodsSetting());

            } else {
                $form = new BossGoodsForm();
                $form->attributes = \Yii::$app->request->get();
                return $this->asJson($form->getBossGoodsSetting());
            }


        } else {


            return $this->render('edit');
        }
    }


}