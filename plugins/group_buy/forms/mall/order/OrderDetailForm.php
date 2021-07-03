<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 文件描述
 * Author: xuyaoxiang
 * Date: 2020/9/21
 * Time: 17:45
 */

namespace app\plugins\group_buy\forms\mall\order;

use app\core\ApiCode;
use app\forms\common\mch\MchSettingForm;
use app\forms\mall\order\OrderDetailForm as ParentOrderDetailForm;
use app\models\CommonOrderDetail;
use app\plugins\group_buy\models\Order;
use app\models\OrderRefund;
use app\models\PriceLog;
use app\models\User;
use app\plugins\mch\models\Mch;

class OrderDetailForm extends ParentOrderDetailForm
{
    public function search()
    {
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }

        $order = Order::find()->where([
            'mall_id' => \Yii::$app->mall->id,
            'id' => $this->order_id,
            'sign'=>'group_buy',
            'is_delete' => 0,
        ])
            ->with('user', 'refund', 'clerk', 'orderClerk', 'store')
            ->with('detail.goods.goodsWarehouse', 'detail.expressRelation')
            ->with('detailExpress.expressRelation.orderDetail.expressRelation')
            ->with('detailExpress.expressSingle')
            ->with('refund')
            ->with('clerk')
            ->with('orderClerk')
            ->with('store')
            ->with('paymentOrder.paymentOrderUnion')
            ->with('active')
            ->with('activeItem')
            ->asArray()
            ->one();

        if (!$order) {
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg' => '订单不存在',
            ];
        }
        $order['refund_info'] = [];
        if ($order['refund']) {
            $order['refund_info'] = $order['refund'][0];
            $order['refund'] = (new OrderRefund())->statusText_business($order['refund'][0]);
        }

        $existsFormIds = [];
        foreach ($order['detail'] as $key => $item) {
            $order['detail'][$key]['goods']['pic_url'] = json_decode($item['goods']['goodsWarehouse']['pic_url'], true);
            $order['detail'][$key]['goods']['cover_pic'] = $item['goods']['goodsWarehouse']['cover_pic'];
            $order['detail'][$key]['attr_list'] = json_decode($item['goods_info'], true)['attr_list'];
            $order['detail'][$key]['goods_info'] = json_decode($item['goods_info']);
            $order['detail'][$key]['form_data'] = $item['form_data'] ? \Yii::$app->serializer->decode($item['form_data']) : null;
            $sameForm = false;
            if ($order['detail'][$key]['form_id']) {
                if (in_array($order['detail'][$key]['form_id'], $existsFormIds)) {
                    $sameForm = true;
                } else {
                    $existsFormIds[] = $order['detail'][$key]['form_id'];
                }
            }
            $order['detail'][$key]['same_form'] = $sameForm;
        }

        foreach ($order['detailExpress'] as &$detailExpress) {
            foreach ($detailExpress['expressRelation'] as &$expressRelation) {
                $expressRelation['orderDetail']['goods_info'] = \Yii::$app->serializer->decode($expressRelation['orderDetail']['goods_info']);
            }
            unset($expressRelation);
        }
        unset($detailExpress);

        $order['order_form'] = json_decode($order['order_form'], true);

        $orderAutoConfirmTime = \Yii::$app->mall->getMallSettingOne('delivery_time');
        $confirmTime = 0;
        if (!empty($order["send_at"])) {
            $confirmTime = strtotime("+{$orderAutoConfirmTime} day", $order["send_at"]);
        }

        //倒计时秒
        $order['auto_cancel'] = $order['is_send'] == 0 ? $order['auto_cancel_at'] - time() : 0;
        $order['auto_confirm'] = $order['is_confirm'] == 0 ? $confirmTime : 0;
        $order['auto_sales'] = ($order['is_confirm'] == 1 && $order['is_sale'] == 0) ? $order['auto_sales_at'] - time() : 0;
        $order['city'] = json_decode($order['city_info'], true);
        $mch = [];
        // 多商户
        if ($order['mch_id'] > 0) {
            $mch = Mch::findOne(['mall_id' => \Yii::$app->mall->id, 'id' => $order['mch_id']]);
        }

        // 控制订单操作 是否显示(例如拼团)
        $order['is_send_show'] = $this->is_send_show;
        $order['is_cancel_show'] = $this->is_cancel_show;
        $order['is_clerk_show'] = $this->is_clerk_show;
        $order['is_confirm_show'] = $this->is_confirm_show;
        $order['action_status'] = (new Order())->getOrderActionStatus($order);
        if (\Yii::$app->admin->identity->mch_id > 0) {
            $mchSettingForm = new MchSettingForm();
            $mchSetting = $mchSettingForm->search();
            $order['is_confirm_show'] = $mchSetting['is_confirm_order'] ? 1 : 0;
        }


        $common_order_detail_list = CommonOrderDetail::find()->where(['order_id' => $this->order_id, 'goods_type' => CommonOrderDetail::TYPE_MALL_GOODS])->asArray()->all();

        $plugin_list = \Yii::$app->plugin->list;
        foreach ($common_order_detail_list as &$item) {
            $price_log_list = PriceLog::find()->alias('p')
                ->where(['p.common_order_detail_id' => $item['id'], 'p.is_delete' => 0])
                ->leftJoin(['u'=>User::tableName()],'u.id=p.user_id')
                ->select('u.nickname,u.avatar_url,p.*')
                ->asArray()->all();


            foreach ($price_log_list as &$log) {
                if ($log['sign'] != 'mall') {
                    $plugin = \Yii::$app->plugin->getPlugin($log['sign']);
                    $log['type_name'] = $plugin->getPriceTypeName($log['id']);
                }
            }
            unset($log);

            $log_list = $price_log_list;
            $newList = [];

            foreach ($plugin_list as $plugin) {
                /**
                 * @var \app\models\Plugin $plugin
                 */
                $newItem['plugin'] = $plugin->name;
                $newItem['display_name'] = $plugin->display_name;
                $is_price = 0;
                $newItem['price_list']=[];
                foreach ($log_list as $log) {
                    if ($log['sign'] == $plugin->name) {
                        $newItem['price_list'][] = $log;
                        // array_push($newItem['price_list'],$log);
                        $is_price = 1;
                    }
                }
                $newItem['is_price'] = $is_price;
                $newList[] = $newItem;
            }
            $item['price_list'] = $newList;
        }

        unset($item);
        unset($log);
        unset($plugin);
        unset($newItem);

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'data' => [
                'order' => $order,
                'mch' => $mch,
                'common_order_detail_list' => $common_order_detail_list
            ]
        ];
    }
}