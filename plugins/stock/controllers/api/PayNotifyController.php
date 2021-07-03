<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * Created by PhpStorm
 * Author: ganxiaohao
 * Date: 2020-08-03
 * Time: 18:04
 */

namespace app\plugins\stock\controllers\api;

use app\controllers\BaseController;
use app\models\Wechat;
use app\plugins\stock\events\FillOrderEvent;
use app\plugins\stock\handlers\FillOrderHandler;
use app\plugins\stock\jobs\FillOrderPriceLogJob;
use app\plugins\stock\models\FillOrder;

class PayNotifyController extends BaseController
{

    public function init()
    {
        parent::init();
        $this->enableCsrfValidation = false;
    }

    /**
     * 微信支付回调
     * @return bool|\Symfony\Component\HttpFoundation\Response
     */
    public function actionWechat()
    {

        try {
            /** @var Wechat $app */
            $app = \Yii::$app->wechat;
            $response = $app->payment->handlePaidNotify(function ($notify, $successful) {

                \Yii::warning(json_encode($notify));

                $t = \Yii::$app->db->beginTransaction();
                try {
                    $res = $notify;
                    if ($res['return_code'] !== 'SUCCESS' && $res['result_code'] !== 'SUCCESS') {
                        \Yii::error('pay_notify 订单尚未支付: ' . $res['result_code']);
                        throw new \Exception('支付失败: ' . $res['out_trade_no']);
                    }
                    $fillOrder = FillOrder::findOne([
                        'order_no' => $res['out_trade_no'],
                    ]);
                    if (!$fillOrder) {
                        \Yii::error('pay_notify 订单不存在: ' . $res['out_trade_no']);
                        throw new \Exception('订单不存在: ' . $res['out_trade_no']);
                    }
                    //判断是否已经支付
                    if ($fillOrder->is_pay === 1) {
                        $t->rollBack();
                        return true;
                    }
                    $fillOrder->is_pay = 1;
                    if ($fillOrder->save()) {
                        $event = new FillOrderEvent();
                        $event->order = $fillOrder;
                        \Yii::$app->trigger(FillOrderHandler::ORDER_PAID, $event);
                        $t->commit();
                        \Yii::warning('stock PayNotifyController 支付回调成功');
                        \Yii::$app->queue->delay(0)->push(new FillOrderPriceLogJob([
                            'order' => $fillOrder,
                            'mall_id' => $fillOrder->mall_id
                        ]));
                        return true;
                    } else {
                        \Yii::warning('stock PayNotifyController 库存商品支付回调成功失败' . json_encode($fillOrder));
                        return false;
                    }
                } catch (\Exception $exception) {
                    $t->rollBack();
                    \Yii::error("pay_notify 支付插件回调处理，出现异常 File=" . $exception->getFile() . ";Line:" . $exception->getLine() . ";message:" . $exception->getMessage());
                    throw new \Exception("支付插件回调处理，更新失败 " . $exception->getMessage());
                }
            });
            return $response;
        } catch (\Exception $ex) {
            \Yii::error("pay_notify 支付回调出现异常 File=" . $ex->getFile() . ";Line:" . $ex->getLine() . ";message:" . $ex->getMessage());
            return false;
        }
    }
}