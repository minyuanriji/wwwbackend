<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 订单详情
 * Author: zal
 * Date: 2020-04-17
 * Time: 14:11
 */

namespace app\forms\mall\order;

use app\core\ApiCode;
use app\forms\common\mch\MchSettingForm;
use app\models\BaseModel;
use app\models\CommonOrderDetail;
use app\models\Order;
use app\models\OrderRefund;
use app\models\PriceLog;
use app\models\User;
use app\plugins\distribution\forms\common\DistributionOrderCommon;
use app\plugins\distribution\Plugin;
use app\plugins\mch\models\Mch;

class OrderDetailForm extends BaseModel
{
    public $order_id;

    // 前端操作 显示设置
    public $is_send_show;
    public $is_cancel_show;
    public $is_clerk_show;
    public $is_confirm_show;

    public function rules()
    {
        return [
            [['order_id'], 'required'],
            [['is_confirm_show', 'is_send_show', 'is_cancel_show', 'is_clerk_show'], 'default', 'value' => 1],
        ];
    }

    /**
     * 搜索
     * @return array
     * @throws \Exception
     */
    public function search()
    {
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }

        $order = Order::find()->where([
            'mall_id' => \Yii::$app->mall->id,
            'id' => $this->order_id,
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

        //  dd($common_order_detail_list);


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

    /**
     * 获取分销订单信息
     * @param $order
     * @return mixed
     * @throws \Exception
     */
    private function getDistributionOrderData($order)
    {
        $plugin = null;
        //是否安装分销插件
        try {
            /** @var Plugin $plugin */
            $plugin = \Yii::$app->plugin->getPlugin('score');
        } catch (\Exception $exception) {
            return $order;
        }
        $firstPrice = 0;
        $secondPrice = 0;
        $thirdPrice = 0;
        $list = $plugin->getDistributionOrderList($order);
        foreach ($list as $index => $item) {
            $firstPrice += $item['first_price'];
            $secondPrice += $item['second_price'];
            $thirdPrice += $item['third_price'];
        }

        $parentId = [];
        foreach ($list as $item) {
            if (!in_array($item['first_parent_id'], $parentId)) {
                $parentId[] = $item['first_parent_id'];
            }
            if (!in_array($item['second_parent_id'], $parentId)) {
                $parentId[] = $item['second_parent_id'];
            }
            if (!in_array($item['third_parent_id'], $parentId)) {
                $parentId[] = $item['third_parent_id'];
            }
        }
        $newShareOrder = $data = [];
        /* @var User[] $parent */
        $parent = User::find()->where(['id' => $parentId])->all();
        foreach ($list as $index => $item) {
            $first = null;
            $second = null;
            $third = null;
            foreach ($parent as $value) {
                if ($value->id == $item['first_parent_id']) {
                    $first = $value;
                }
                if ($value->id == $item['second_parent_id']) {
                    $second = $value;
                }
                if ($value->id == $item['third_parent_id']) {
                    $third = $value;
                }
            }
            $data['first_parent'] = [
                'nickname' => $first->nickname,
                'name' => $first->nickname,
                'mobile' => $first->mobile ? $first->mobile : '',
            ];
            $data['second_parent'] = $second ? [
                'nickname' => $second->nickname,
                'name' => $second->nickname,
                'mobile' => $second->mobile,
            ] : null;
            $data['third_parent'] = $third ? [
                'nickname' => $third->nickname,
                'name' => $third->nickname,
                'mobile' => $third->mobile
            ] : null;

            $newShareItem = $item;
            $data['is_zigou'] = $item['user_id'] == $item['first_parent_id'] ? 1 : 0;
            $newShareOrder = $data;
        }
        $newShareOrder['first_price'] = $firstPrice;
        $newShareOrder['second_price'] = $secondPrice;
        //$newShareOrder['second_parent_id'] = $secondPrice;
        $newShareOrder['third_price'] = $thirdPrice;
        $order['shareOrder'] = [$newShareOrder];
        return $order;
    }
}
