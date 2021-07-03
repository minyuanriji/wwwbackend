<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 订单管理-订单列表
 * Author: zal
 * Date: 2020-04-16
 * Time: 14:50
 */

namespace app\controllers\mall;

use app\core\ApiCode;
use app\forms\common\order\OrderCancelForm;
use app\forms\common\order\OrderPriceForm;
use app\forms\common\order\OrderRefundForm;
use app\forms\common\order\PrintForm;
use app\forms\mall\order\OrderForm;
use app\forms\mall\order\OrderDetailForm;
use app\forms\mall\order\OrderSendForm;
use app\forms\mall\order\OrderClerkForm;
use app\forms\mall\order\OrderUpdateAddressForm;
use app\forms\mall\order\OrderDestroyForm;
use app\forms\mall\order\OrderRefundListForm;
use app\forms\mall\order\OrderPrintForm;
use app\core\CsvExport;
use app\logic\IntegralLogic;
use app\models\Order;

class OrderController extends OrderManagerController
{
    public function actionIndex()
    { 
//        \Yii::$app->log->targets['debug'] = null;
        if (\Yii::$app->request->isAjax) {
            $form = new OrderForm();
            $form->attributes = \Yii::$app->request->get();
            $form->attributes = \Yii::$app->request->post();
            return $this->asJson($form->search());
        } else {
            if (\Yii::$app->request->post('flag') === 'EXPORT') {
                $fields = explode(',', \Yii::$app->request->post('fields'));
                $form = new OrderForm();
                $form->attributes = \Yii::$app->request->get();
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
     * 订单总数
     * @return string|\yii\web\Response
     * @throws \Exception
     */
    public function actionOrderCount()
    {
        if (\Yii::$app->request->isAjax) {
            $order = new OrderForm();
            $order->status = 0;//未付款总数
            $unpaid_count = $order->search_num();
            $order->status = 1;//代发货总数
            $consignment_count = $order->search_num();
            $order->status = 2;//待收货总数
            $received_count = $order->search_num();
            return $this->asJson(
                [
                    'code' => ApiCode::CODE_SUCCESS,
                    'data' => [
                        'unpaid_count' => $unpaid_count,
                        'consignment_count' => $consignment_count,
                        'received_count' => $received_count
                    ]
                ]);
        } else {
            return $this->render('index');
        }
    }

    /**
     * 订单详情
     * @return string|\yii\web\Response
     * @throws \Exception
     */
    public function actionDetail()
    {
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
     * 添加备注
     * @return array
     */
    public function actionSellerRemark()
    {
        if (\Yii::$app->request->isPost) {
            $form = new OrderForm();
            // $form->order_id = 236;
            // $form->seller_remark = '123';商家备注
            $form->attributes = \Yii::$app->request->post();
            return $form->sellerRemark();
        }
    }

    /**
     * 订单取消
     * @return \yii\web\Response
     */
    public function actionCancel()
    {
        if (\Yii::$app->request->isPost) {
            $form = new OrderCancelForm();
            $form->attributes = \Yii::$app->request->post();
            return $this->asJson($form->save());
        }
    }

    /**
     * 订单删除
     * @return \yii\web\Response
     */
    public function actionDestroy()
    {
        if ($order_id = \Yii::$app->request->post('order_id')) {
            $form = new OrderDestroyForm();
            $form->order_id = $order_id;
            return $this->asJson($form->destroy());
        }
    }

    /**
     * 回收站
     * @return \yii\web\Response
     */
    public function actionRecycle()
    {
        if (\Yii::$app->request->isPost) {
            $form = new OrderDestroyForm();
            //$form->order_id = 236;
            //$form->is_recycle = 1; 1回收 0恢复
            $form->attributes = \Yii::$app->request->post();
            return $this->asJson($form->recycle());
        }
    }

    /**
     * 清空回收站
     * @return \yii\web\Response
     */
    public function actionDestroyAll()
    {
        if (\Yii::$app->request->isPost) {
            $form = new OrderDestroyForm();
            return $this->asJson($form->destroyAll());
        }
    }

    /**
     * 订单发货
     * @return \yii\web\Response
     */
    public function actionSend()
    {
        if (\Yii::$app->request->isPost) {
            $form = new \app\forms\common\order\OrderSendForm();
            $form->attributes = \Yii::$app->request->post();
            return $this->asJson($form->save());
        }
    }

    /**
     * 批量发货
     * @return array|string|\yii\web\Response
     */
    public function actionBatchSend()
    {
        if (\Yii::$app->request->isAjax) {
            $form = new OrderSendForm();
            if (\Yii::$app->request->isPost) {
                $form->is_express = 1;
                $form->attributes = \Yii::$app->request->post();
                return $form->batchSave();
            } else {
                $form->attributes = \Yii::$app->request->get();
                return $this->asJson($form->batchDetail());
            }
        } else {
            return $this->render('batch-send');
        }
    }

    /**
     * 默认模板下载
     */
    public function actionBatchSendModel()
    {
        $csv = new CsvExport();
        $fileName = date('YmdHis', time());
        $headlist = ["序号(可不填)", "订单号", "快递单号"];
        return $csv->export([], $headlist, $fileName);
    }

    /**
     * 修改运费
     * @return \yii\web\Response
     */
    public function actionUpdateExpress()
    {
        if (\Yii::$app->request->isPost) {
            $form = new OrderPriceForm();
            $form->attributes = \Yii::$app->request->post();
            return $this->asJson($form->updateExpress());
        }
    }

    /**
     * 修改价格
     * @return \yii\web\Response
     */
    public function actionUpdatePrice()
    {
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
    public function actionUpdateTotalPrice()
    {
        if (\Yii::$app->request->isPost) {
            $form = new OrderPriceForm();
            $form->attributes = \Yii::$app->request->post();
            return $this->asJson($form->updateTotalPrice());
        }
    }

    /**
     * 货到付款，确认收货
     * @return \yii\web\Response
     */
    public function actionConfirm()
    {
        if (\Yii::$app->request->isPost) {
            $form = new OrderForm();
            $form->attributes = \Yii::$app->request->post();
            return $this->asJson($form->confirm());
        }
    }

    /**
     * 更新订单地址
     * @return \yii\web\Response
     */
    public function actionUpdateAddress()
    {
        if (\Yii::$app->request->isPost) {
            $form = new OrderUpdateAddressForm();
            $form->attributes = \Yii::$app->request->post();
            return $this->asJson($form->save());
        }
    }

    /**
     * 获取面单
     * @return \yii\web\Response
     */
    public function actionPrint()
    {
        if (\Yii::$app->request->isAjax) {
            $form = new PrintForm();
            $form->attributes = \Yii::$app->request->get();
            return $this->asJson($form->save());
        }
    }

    /**
     * 小票打印
     * @return \yii\web\Response
     */
    public function actionOrderPrint()
    {
        if (\Yii::$app->request->isAjax) {
            $form = new OrderPrintForm();
            $form->attributes = \Yii::$app->request->get();
            return $this->asJson($form->save());
        }
    }

    /**
     * 售后订单列表
     * @return bool|string|\yii\web\Response
     */
    public function actionRefund()
    {
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
     * 售后订单详情
     * @return string|\yii\web\Response
     */
    public function actionRefundDetail()
    {
        if (\Yii::$app->request->isAjax) {
            $form = new OrderRefundListForm();
            $form->attributes = \Yii::$app->request->get();
            return $this->asJson($form->refundDetail());
        } else {
            return $this->render('refund-detail');
        }
    }

    /**
     * 自提订单列表
     * @return bool|string|\yii\web\Response
     */
    public function actionOffline()
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
                $form->send_type = 1;
                $form->fields = $fields;
                $form->search();
                return false;
            } else {
                return $this->render('offline');
            }
        }
    }

    /**
     * 售后退款订单，商家确认收货
     * @return \yii\web\Response
     */
    public function actionShouHuo()
    {
        if (\Yii::$app->request->isPost) {
            $form = new \app\forms\mall\order\OrderRefundForm();
            $form->attributes = \Yii::$app->request->post();
            return $this->asJson($form->shouHuo());
        }
    }

    /**
     * 处理售后订单
     * @return \yii\web\Response
     */
    public function actionRefundHandle()
    {
        if (\Yii::$app->request->isPost) {
            $form = new OrderRefundForm();
            $form->attributes = \Yii::$app->request->post();
            return $this->asJson($form->save());
        }
    }

    /**
     * 收货地址列表
     * @return \yii\web\Response
     */
    public function actionAddressList()
    {
        if (\Yii::$app->request->isAjax) {
            $form = new OrderForm();
            return $this->asJson($form->addressList());
        }
    }

    /**
     * 订单核销确认收款
     * @return \yii\web\Response
     */
    public function actionClerkAffirmPay()
    {
        $form = new OrderClerkForm();
        $form->attributes = \Yii::$app->request->get();

        return $this->asJson($form->affirmPay());
    }

    /**
     * 订单核销
     * @return \yii\web\Response
     */
    public function actionOrderClerk()
    {
        $form = new OrderClerkForm();
        $form->attributes = \Yii::$app->request->post();

        return $this->asJson($form->OrderClerk());
    }

    /**
     * 售后订单(结束订单)
     * @return \yii\web\Response
     */
    public function actionOrderSales()
    {
        if (\Yii::$app->request->isPost) {
            $form = new OrderForm();
            $form->attributes = \Yii::$app->request->post();
            return $this->asJson($form->orderSales());
        }
    }
}
