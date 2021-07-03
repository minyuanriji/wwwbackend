<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * Created by PhpStorm
 * Author: ganxiaohao
 * Date: 2020-04-20
 * Time: 15:40
 */

namespace app\controllers\mall;


class DemoController extends MallController
{

    public function actionIndex()
    {

        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isPost) {
            } else {

            }
        } else {

            return $this->render('index');

        }
    }

}