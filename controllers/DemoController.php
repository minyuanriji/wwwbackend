<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * Created by PhpStorm
 * Author: ganxiaohao
 * Date: 2020-04-20
 * Time: 17:01
 */

namespace app\controllers;


use app\controllers\mall\MallController;
use app\core\ApiCode;

class DemoController extends MallController
{

    /**
     * @Author: 广东七件事 ganxiaohao
     * @Date: 2020-04-24
     * @Time: 9:18
     * @Note:demo
     * @return string|\yii\web\Response
     */
    public function actionIndex()
    {
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isPost) {
                return $this->asJson(['code' => ApiCode::CODE_SUCCESS, 'msg' => 'post']);
            } else {
                return $this->asJson(['code' => ApiCode::CODE_SUCCESS, 'msg' => 'get']);
            }
        } else {
            return $this->render('index');
        }
    }

    public function actionForm()
    {
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isPost) {
                return $this->asJson(['code' => ApiCode::CODE_SUCCESS, 'msg' => 'post']);
            } else {
                return $this->asJson(['code' => ApiCode::CODE_SUCCESS, 'msg' => 'get']);
            }
        } else {
            return $this->render('form');
        }
    }


}