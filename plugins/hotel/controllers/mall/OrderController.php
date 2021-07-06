<?php
namespace app\plugins\hotel\controllers\mall;


use app\plugins\Controller;
use app\plugins\hotel\forms\mall\HotelOrderListForm;
use app\plugins\hotel\forms\mall\HotelOrderRefundApplyForm;
use app\plugins\hotel\forms\mall\HotelOrderRefundListForm;

class OrderController extends Controller{

    /**
     * 酒店预订单列表
     * @return string|\yii\web\Response
     */
    public function actionList(){
        if (\Yii::$app->request->isAjax) {
            $form = new HotelOrderListForm();
            $form->attributes = \Yii::$app->request->get();
            return $this->asJson($form->getList());
        } else {
            return $this->render('index');
        }
    }

    /**
     * 酒店售后订单
     * @return string|\yii\web\Response
     */
    public function actionRefund(){
        if (\Yii::$app->request->isAjax) {
            $form = new HotelOrderRefundListForm();
            $form->attributes = \Yii::$app->request->get();
            return $this->asJson($form->getList());
        } else {
            return $this->render('refund');
        }
    }

    /**
     * 售后订单操作
     * @return string|\yii\web\Response
     */
    public function actionRefundApply(){
        $form = new HotelOrderRefundApplyForm();
        $form->attributes = \Yii::$app->request->post();
        return $this->asJson($form->apply());
    }
}