<?php

namespace app\plugins\taolijin\controllers\mall;

use app\plugins\Controller;
use app\plugins\taolijin\forms\mall\TaoLiJinOrderAddForm;
use app\plugins\taolijin\forms\mall\TaoLiJinOrderDeleteForm;
use app\plugins\taolijin\forms\mall\TaoLiJinOrderDoFinishForm;
use app\plugins\taolijin\forms\mall\TaoLiJinOrderEditForm;
use app\plugins\taolijin\forms\mall\TaoLiJinOrderListForm;
use app\plugins\taolijin\forms\mall\TaoLiJinOrderSearchUserForm;

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

    /**
     * 搜索用户
     * @return \yii\web\Response
     */
    public function actionSearchUser(){
        $form = new TaoLiJinOrderSearchUserForm();
        $form->attributes = \Yii::$app->request->post();
        return $this->asJson($form->search());
    }

    /**
     * 订单录入
     * @return \yii\web\Response
     */
    public function actionAdd(){
        $form = new TaoLiJinOrderAddForm();
        $form->attributes = \Yii::$app->request->post();
        return $this->asJson($form->save());
    }

    /**
     * 修改订单信息
     * @return \yii\web\Response
     */
    public function actionEdit(){
        $form = new TaoLiJinOrderEditForm();
        $form->attributes = \Yii::$app->request->post();
        return $this->asJson($form->save());
    }

    /**
     * 删除订单
     * @return \yii\web\Response
     */
    public function actionDelete(){
        $form = new TaoLiJinOrderDeleteForm();
        $form->attributes = \Yii::$app->request->post();
        return $this->asJson($form->delete());
    }

    public function actionDoFinish(){
        $form = new TaoLiJinOrderDoFinishForm();
        $form->attributes = \Yii::$app->request->post();
        return $this->asJson($form->doFinish());
    }
}