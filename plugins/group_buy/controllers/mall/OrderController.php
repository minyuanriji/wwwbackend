<?php

namespace app\plugins\group_buy\controllers\mall;

use app\plugins\group_buy\forms\mall\order\OrderDetailForm;
use app\plugins\group_buy\forms\mall\order\OrderForm;
use app\plugins\Controller;

class OrderController extends Controller
{
    public $enableCsrfValidation = false;

    //订单详情
    public function actionDetail()
    {
        return $this->render('detail');
    }

    public function actionApiDetail()
    {
        $form             = new OrderDetailForm();
        $form->attributes = \Yii::$app->request->get();
        $res              = $form->search();
        return $this->asJson($res);
    }

    //订单列表
    public function actionList()
    {
        if (\Yii::$app->request->isAjax) {
            $form = new OrderForm();
            $form->attributes = \Yii::$app->request->get();
            $form->attributes = \Yii::$app->request->post();
            return $this->asJson($form->search());
        } else {
            if (\Yii::$app->request->post('flag') === 'EXPORT') {
                $fields = explode(',', \Yii::$app->request->post('fields'));
                $form = new OrderForm();
                $form->attributes = \Yii::$app->request->post();
                $form->fields = $fields;
                $form->search();
                return false;
            } else {
                return $this->render('list');
            }
        }
    }

    public function actionIndex()
    {
        $form             = new OrderForm();
        $form->attributes = \Yii::$app->request->get();
        $form->attributes = \Yii::$app->request->post();
        return $this->asJson($form->search());
    }
}