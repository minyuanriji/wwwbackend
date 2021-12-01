<?php

namespace app\plugins\taolijin\controllers\mall;

use app\plugins\Controller;
use app\plugins\taolijin\forms\mall\TaoLiJinOrderListForm;

class OrderController extends Controller{

    /**
     * 商品列表
     * @return string|\yii\web\Response
     */
    public function actionList(){

        if (\Yii::$app->request->isAjax) {
            $form = new TaoLiJinOrderListForm();
            $form->attributes = \Yii::$app->request->get();
            return $this->asJson($form->getList());
        } else {
            return $this->render('index');
        }
    }

}