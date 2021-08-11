<?php
namespace app\mch\controllers;

use app\forms\common\order\OrderCancelForm;
use app\forms\common\order\OrderPriceForm;
use app\forms\common\order\OrderRefundForm;
use app\forms\common\order\OrderSendForm;
use app\forms\mall\order\OrderDestroyForm;
use app\forms\mall\order\OrderDetailForm;
use app\forms\mall\order\OrderForm;
use app\forms\mall\order\OrderPrintForm;
use app\forms\mall\order\OrderRefundListForm;
use app\forms\mall\order\OrderUpdateAddressForm;

class OrderController extends MchController {

    public function actionIndex(){
        if (\Yii::$app->request->isAjax) {
            $form = new OrderForm();

            $_GET['date_start'] = !empty($_GET['date_start']) ? strtotime($_GET['date_start']) : "";
            $_GET['date_end'] = !empty($_GET['date_end']) ? strtotime($_GET['date_end']) : "";
            $_POST['date_start'] = !empty($_POST['date_start']) ? strtotime($_POST['date_start']) : "";
            $_POST['date_end'] = !empty($_POST['date_end']) ? strtotime($_POST['date_end']) : "";

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
                return $this->render('index');
            }
        }
    }

    /**
     * 回收站
     * @return \yii\web\Response
     */
    public function actionRecycle(){
        if (\Yii::$app->request->isPost) {
            $form = new OrderDestroyForm();
            $form->attributes = \Yii::$app->request->post();
            return $this->asJson($form->recycle());
        }
    }

    /**
     * 修改价格
     * @return \yii\web\Response
     */
    public function actionUpdatePrice(){
        if (\Yii::$app->request->isPost) {
            $form = new OrderPriceForm();
            $form->attributes = \Yii::$app->request->post();
            return $this->asJson($form->updatePrice());
        }
    }

    /**
     * 修改总价格
     * @return \yii\web\Response
     */
    public function actionUpdateTotalPrice(){
        if (\Yii::$app->request->isPost) {
            $form = new OrderPriceForm();
            $form->attributes = \Yii::$app->request->post();
            return $this->asJson($form->updateTotalPrice());
        }
    }

    /**
     * 小票打印
     * @return \yii\web\Response
     */
    public function actionOrderPrint(){
        if (\Yii::$app->request->isAjax) {
            $form = new OrderPrintForm();
            $form->attributes = \Yii::$app->request->get();
            return $this->asJson($form->save());
        }
    }

    /**
     * 添加备注
     * @return array
     */
    public function actionSellerRemark(){
        if (\Yii::$app->request->isPost) {
            $form = new OrderForm();
            $form->attributes = \Yii::$app->request->post();
            return $form->sellerRemark();
        }
    }

    /**
     * 订单发货
     * @return \yii\web\Response
     */
    public function actionSend(){
        if (\Yii::$app->request->isPost) {
            $form = new OrderSendForm();
            $form->mch_id = \Yii::$app->admin->identity->mch_id;
            $form->attributes = \Yii::$app->request->post();
            return $this->asJson($form->save());
        }
    }

    /**
     * 订单取消
     * @return \yii\web\Response
     */
    public function actionCancel(){
        if (\Yii::$app->request->isPost) {
            $form = new OrderCancelForm();
            $form->mch_id = \Yii::$app->admin->identity->mch_id;
            $form->attributes = \Yii::$app->request->post();
            return $this->asJson($form->save());
        }
    }

    /**
     * 收货地址列表
     * @return \yii\web\Response
     */
    public function actionAddressList(){
        if (\Yii::$app->request->isAjax) {
            $form = new OrderForm();
            return $this->asJson($form->addressList());
        }
    }

    /**
     * 更新订单地址
     * @return \yii\web\Response
     */
    public function actionUpdateAddress(){
        if (\Yii::$app->request->isPost) {
            $form = new OrderUpdateAddressForm();
            $form->attributes = \Yii::$app->request->post();
            return $this->asJson($form->save());
        }
    }

    /**
     * 货到付款，确认收货
     * @return \yii\web\Response
     */
    public function actionConfirm(){
        if (\Yii::$app->request->isPost) {
            $form = new OrderForm();
            $form->attributes = \Yii::$app->request->post();
            return $this->asJson($form->confirm());
        }
    }

    /**
     * 订单删除
     * @return \yii\web\Response
     */
    public function actionDestroy(){
        if ($order_id = \Yii::$app->request->post('order_id')) {
            $form = new OrderDestroyForm();
            $form->order_id = $order_id;
            return $this->asJson($form->destroy());
        }
    }

    /**
     * 清空回收站
     * @return \yii\web\Response
     */
    public function actionDestroyAll(){
        if (\Yii::$app->request->isPost) {
            $form = new OrderDestroyForm();
            return $this->asJson($form->destroyAll());
        }
    }

    /**
     * 订单详情
     * @return string|\yii\web\Response
     * @throws \Exception
     */
    public function actionDetail(){
        if (\Yii::$app->request->isAjax) {
            $form = new OrderDetailForm();
            $form->attributes = \Yii::$app->request->get();
            $res = $form->search();
            return $this->asJson($res);
        } else {
            return $this->render('detail');
        }
    }

    /**
     * 售后订单(结束订单)
     * @return \yii\web\Response
     */
    public function actionOrderSales(){
        if (\Yii::$app->request->isPost) {
            $form = new OrderForm();
            $form->attributes = \Yii::$app->request->post();
            return $this->asJson($form->orderSales());
        }
    }

    /**
     * 售后订单列表
     * @return bool|string|\yii\web\Response
     */
    public function actionRefund() {
        if (\Yii::$app->request->isAjax) {
            $form = new OrderRefundListForm();
            $form->attributes = \Yii::$app->request->get();
            return $this->asJson($form->search());
        } else {
            if (\Yii::$app->request->post('flag') === 'EXPORT') {
                $fields = explode(',', \Yii::$app->request->post('fields'));
                $form = new OrderRefundListForm();
                $form->attributes = \Yii::$app->request->post();
                $form->fields = $fields;
                $form->search();
                return false;
            } else {
                return $this->render('refund');
            }
        }
    }

    /**
     * 处理售后订单
     * @return \yii\web\Response
     */
    public function actionRefundHandle(){
        if (\Yii::$app->request->isPost) {
            $form = new OrderRefundForm();
            $form->attributes = \Yii::$app->request->post();

            return $this->asJson($form->save());
        }
    }

    /**
     * 售后订单详情
     * @return string|\yii\web\Response
     */
    public function actionRefundDetail(){
        if (\Yii::$app->request->isAjax) {
            $form = new OrderRefundListForm();
            $form->attributes = \Yii::$app->request->get();
            return $this->asJson($form->refundDetail());
        } else {
            return $this->render('refund-detail');
        }
    }
}
