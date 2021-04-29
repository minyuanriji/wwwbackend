<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 文件描述
 * Author: xuyaoxiang
 * Date: 2020/9/18
 * Time: 20:48
 */

namespace app\plugins\group_buy\services;

use app\events\OrderEvent;
use app\models\Order;
use app\models\OrderDetail;
use app\plugins\group_buy\models\PluginGroupBuyActive;
use app\plugins\group_buy\models\PluginGroupBuyActiveItem;
use app\plugins\group_buy\forms\common\ActiveItemQueryCommonForm;
use app\services\wechat\WechatTemplateService;
use app\plugins\group_buy\event\ActiveEvent;
class ActiveServices
{
    use ReturnData;

    public function timeEnd($active_id)
    {
        $model = PluginGroupBuyActive::find()->where(['status' => 1, 'id' => $active_id])->one();
        if (!$model) {
            return $this->returnApiResultData(99, "找不到拼团");
        }

        //是否虚拟成团
        if ($model->is_virtual) {
            //无论多少人都成团
            return $this->virtualEnd($model);
        } else {
            return $this->activeFailed($model);
        }
    }

    public function activeFailed($model)
    {
        $t = \Yii::$app->db->beginTransaction();
        try {
            //拼团失败
            $model->status = 3;

            //拼单,找出没有取消订单的
            $ActiveItemQueryCommonForm                = new ActiveItemQueryCommonForm();
            $ActiveItemQueryCommonForm->is_page       = false;
            $ActiveItemQueryCommonForm->as_array      = false;
            $ActiveItemQueryCommonForm->active_id     = $model->id;
            $ActiveItemQueryCommonForm->cancel_status = 0;
            $ActiveItemQueryCommonForm->mall_id       = $model->mall_id;
            $active_items                             = $ActiveItemQueryCommonForm->returnAll();

            foreach ($active_items as $value) {
                //取消订单人数
                $model->cancel_people += 1;
                $this->orderCancel($value->order);
            }

            $model->save();

            $t->commit();

            //拼团失败事件触发
            $event                          = new ActiveEvent();
            $event->plugin_group_buy_active = $model;
            \Yii::$app->trigger(PluginGroupBuyActive::EVENT_GROUP_BUY_ACTIVE_FAILED, $event);

        } catch (\Exception $e) {

            $t->rollBack();

            return $this->returnApiResultData(98, $e->getMessage());
        }

        return $this->returnApiResultData(0, "拼团退款成功");
    }

    public function virtualEnd($model)
    {
        $model->status = 2;
        $model->save();

        $event                          = new ActiveEvent();
        $event->plugin_group_buy_active = $model;
        \Yii::$app->trigger(PluginGroupBuyActive::EVENT_GROUP_BUY_ACTIVE_SUCCESS, $event);

        return $this->returnApiResultData(0, "拼团成功.");
    }

    private function orderCancel($order)
    {
        $order->status        = Order::STATUS_CLOSE;
        $order->cancel_status = 1;
        $order->cancel_at     = time();
        if ($order->save()) {
            $event = new OrderEvent([
                'order' => $order,
            ]);
            \Yii::$app->trigger(Order::EVENT_CANCELED, $event);
        }
    }

    public function sendWechatTempSuccess(PluginGroupBuyActiveItem $PluginGroupBuyActiveItem)
    {
        $WechatTemplateService = new WechatTemplateService($PluginGroupBuyActiveItem->mall_id);

        $url = "/pages/order/detail?orderId=" . $PluginGroupBuyActiveItem->order->id . '&active_status=2';

        $h5_url = \Yii::$app->params['web_url'] . "/h5/#" . $url;

        $platform = $WechatTemplateService->getPlatForm();

        $OrderDetail = new OrderDetail();
        $goods_info    = $OrderDetail->decodeGoodsInfo($PluginGroupBuyActiveItem->order->detail[0]['goods_info']);

        $send_data   = [
            'first'    => '您好,您参与的拼团拼团成功',
            'keyword1' => $PluginGroupBuyActiveItem->order->order_no,
            'keyword2' => $goods_info['goods_attr']['name'],
            'remark'   => '感谢您在此购物成功，同时希望您的再次光临！'
        ];

        return $WechatTemplateService->send($PluginGroupBuyActiveItem->user_id, WechatTemplateService::TEM_KEY['group_buy_success']['tem_key'], $h5_url, $send_data, $platform, $url);
    }

    public function sendWechatTempFailed(PluginGroupBuyActiveItem $PluginGroupBuyActiveItem)
    {
        $WechatTemplateService = new WechatTemplateService($PluginGroupBuyActiveItem->mall_id);

        $url = "/pages/order/detail?orderId=" . $PluginGroupBuyActiveItem->order->id . '&active_status=3';

        $h5_url = \Yii::$app->params['web_url'] . "/h5/#" . $url;

        $platform = $WechatTemplateService->getPlatForm();

        $OrderDetail = new OrderDetail();
        $goods_info    = $OrderDetail->decodeGoodsInfo($PluginGroupBuyActiveItem->order->detail[0]['goods_info']);

        $send_data   = [
            'first'    => '您参加的拼团因人数不足而组团失败。我们将为您安排退款事宜。',
            'keyword1' => $goods_info['goods_attr']['name'],
            'keyword2' => $PluginGroupBuyActiveItem->order->total_pay_price,
            'keyword3' => $PluginGroupBuyActiveItem->order->total_pay_price,
            'remark'   => '时间:'.date("Y-m-d H:i:s")
        ];

        return $WechatTemplateService->send($PluginGroupBuyActiveItem->user_id, WechatTemplateService::TEM_KEY['group_buy_failed']['tem_key'], $h5_url, $send_data, $platform, $url);
    }
}