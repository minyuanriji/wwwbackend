<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 创建订单基础处理类
 * Author: zal
 * Date: 2020-04-21
 * Time: 11:10
 */

namespace app\handlers\orderHandler;

use app\forms\common\distribution\AddDistributionOrder;
use app\forms\common\distribution\DistributionCommon;
use app\forms\common\prints\Exceptions\PrintException;
use app\forms\common\prints\PrintOrder;
use app\component\jobs\OrderCancelJob;
use app\models\Cart;
use app\models\User;
use app\services\wechat\WechatTemplateService;
use yii\log\Logger;

/**
 * @property User $user
 */
abstract class BaseOrderCreatedHandler extends BaseOrderHandler
{
    public $user;

    /**
     * 自动取消
     * @return $this
     */
    protected function setAutoCancel()
    {
        $orderAutoCancelMinute = \Yii::$app->mall->getMallSettingOne('over_time');
        if (is_numeric($orderAutoCancelMinute) && $orderAutoCancelMinute > 0) {
            // 订单自动取消任务
            \Yii::$app->queue->delay($orderAutoCancelMinute * 60)->push(new OrderCancelJob([
                'orderId' => $this->event->order->id,
            ]));
            $autoCancelTime = $this->event->order->created_at + $orderAutoCancelMinute * 60;
            $this->event->order->auto_cancel_at = $autoCancelTime;
            $this->event->order->save();
        }
        return $this;
    }

    /**
     * 设置打印数据
     * @return $this
     */
    protected function setPrint()
    {
        $orderConfig = $this->orderConfig;

        try {
            if ($orderConfig->is_print != 1) {
                throw new PrintException($this->event->order->sign . '未开启小票打印');
            }
            (new PrintOrder())->print($this->event->order, $this->event->order->id, 'order');
        } catch (PrintException $exception) {
            \Yii::error("小票打印打印出错：" . $exception->getMessage());
        }
        return $this;
    }

    /**
     * 删除购物车已购买的商品
     */
    protected function deleteCartGoods()
    {
        Cart::updateAll(['is_delete' => 1], ['id' => $this->event->cartIds]);
        return $this;
    }

    protected function sendWechatTemp(){
        $WechatTemplateService = new WechatTemplateService($this->event->order->mall_id);

        $url="/pages/order/detail?orderId=".$this->event->order->id;

        $h5_url = \Yii::$app->params['web_url'] . "/h5/#".$url;

        $platform = $WechatTemplateService->getPlatForm();

        $send_data = [
            'first'    => '您好，您的订单已生成',
            'keyword1' => date("Y-m-d H:i:s",$this->event->order->created_at),
            'keyword2' => $this->event->orderItem['goods_list'][0]['name'],
            'keyword3' => $this->event->order->order_no,
            'remark'   => '感谢您的使用'
        ];

        $WechatTemplateService->send($this->event->order->user_id, WechatTemplateService::TEM_KEY['order_create']['tem_key'], $h5_url, $send_data, $platform,$url);

        return $this;
    }


}
