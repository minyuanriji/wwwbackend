<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 订单提交任务
 * Author: zal
 * Date: 2020-04-08
 * Time: 15:16
 */

namespace app\component\jobs;

use app\events\OrderEvent;
use app\forms\api\order\CommonOrderForm;
use app\forms\api\order\OrderSubmitForm;
use app\models\BaseModel;
use app\models\CommonOrderDetail;
use app\models\Mall;
use app\models\Order;
use app\models\OrderSubmitResult;
use app\models\User;
use app\models\UserCoupon;
use app\plugins\mch\models\MchOrder;
use yii\base\BaseObject;
use yii\queue\JobInterface;
use yii\queue\Queue;

class OrderSubmitJob extends BaseObject implements JobInterface
{
    /** @var Mall $mall */
    public $mall;

    /** @var User $user */
    public $user;

    /** @var array $data */
    public $form_data;

    /** @var string $token */
    public $token;

    /** @var array 返回数据 */
    public $return_data;

    public $sign;
    public $supportPayTypes;
    public $enableMemberPrice;
    public $enableCoupon;
    public $enableScore;
    public $enableOrderForm;
    public $enablePriceEnable;
    public $enableAddressEnable;
    public $status;
    public $appVersion;

    /** @var string $OrderSubmitFormClass */
    public $OrderSubmitFormClass;

    /**
     * @param Queue $queue which pushed and is handling the job
     * @throws \Throwable
     * @throws \yii\db\Exception
     */
    public function execute($queue)
    {
        \Yii::warning('order_submit 开始:');
        \Yii::$app->user->setIdentity($this->user);
        \Yii::$app->setMall($this->mall);
        \Yii::$app->setAppVersion($this->appVersion);
        \Yii::$app->setAppPlatform($this->user->platform);

        $t = \Yii::$app->db->beginTransaction();
        try {
            $oldOrder = Order::findOne(['token' => $this->token, 'sign' => $this->sign, 'is_delete' => 0]);
            if ($oldOrder) {
                throw new \Exception('重复下单。');
            }
            /** @var OrderSubmitForm $form */
            $form = new $this->OrderSubmitFormClass();
            $form->form_data = $this->form_data;
            \Yii::warning('order_submit form_data:' . json_encode($this->form_data));
            $form->setSign($this->sign)
                ->setEnableMemberPrice($this->enableMemberPrice)
                ->setEnableCoupon($this->enableCoupon)
                ->setEnableScore($this->enableScore)
                ->setEnablePriceEnable($this->enablePriceEnable)
                ->setEnableAddressEnable($this->enableAddressEnable)
                ->setEnableOrderForm($this->enableOrderForm);
            \Yii::warning('order_submit set');
            $data = $this->return_data;
            \Yii::warning('order_submit data:' . json_encode($data));
            if (!$data['user_address_enable']) {
                throw new \Exception('当前收货地址不允许购买。');
            }
            if (!$data['price_enable']) {
                throw new \Exception('订单总价未达到起送要求。');
            }
            $user = \Yii::$app->user->identity;
            $commonOrderJobs = [];
            foreach ($data['list'] as $orderItem) {
                $order = new Order();
                \Yii::warning('order_submit foreach 1');
                $order->mall_id = \Yii::$app->mall->id;
                \Yii::warning('order_submit foreach 11');
                $order->user_id = $user->getId();
                $order->mch_id = $orderItem['mch']['id'];
                $order->order_no = Order::getOrderNo('S');;
                \Yii::warning('order_submit foreach 2');
                $order->total_price = $orderItem['total_price'];
                $order->total_pay_price = $orderItem['total_price'];
                $order->express_original_price = $orderItem['express_price'];
                $order->express_price = $orderItem['express_price'];
                $order->total_goods_price = $orderItem['total_goods_price'];
                $order->total_goods_original_price = $orderItem['total_goods_original_price'];
                \Yii::warning('order_submit foreach 3');
                $order->member_discount_price = $orderItem['member_discount'];
                $order->use_user_coupon_id = 0;
                $order->coupon_discount_price = 0;
                $order->use_score = $orderItem['score']['use'] ? $orderItem['score']['use_num'] : 0;
                $order->score_deduction_price = $orderItem['score']['use'] ? $orderItem['score']['deduction_price'] : 0;
                \Yii::warning('order_submit foreach 4');
                $order->name = $data['user_address']['name'];
                $order->mobile = $data['user_address']['mobile'];
                if ($orderItem['delivery']['send_type'] !== 'offline') {
                    $order->address = $data['user_address']['province']
                        . ' '
                        . $data['user_address']['city']
                        . ' '
                        . $data['user_address']['district']
                        . ' '
                        . $data['user_address']['detail'];

                    $order->address_id=$data['user_address']['id'];
                }
                \Yii::warning('order_submit foreach 5');
                $order->remark = empty($orderItem['remark']) ? "" : $orderItem['remark'];
                $order->order_form = $order->encodeOrderForm($orderItem['order_form_data']);
                $order->distance = isset($orderItem['form_data']['distance']) ? $orderItem['form_data']['distance'] : 0;//同城距离
                $order->words = '';

                $order->is_pay = Order::IS_PAY_NO;
                $order->pay_type = Order::IS_PAY_NO;
                $order->is_send = 0;
                $order->is_confirm = Order::IS_COMMENT_NO;
                $order->is_sale = 0;
                $order->support_pay_types = $order->encodeSupportPayTypes($this->supportPayTypes);
                \Yii::warning('order_submit foreach 6');
                if ($orderItem['delivery']['send_type'] === 'offline') {
                    if (empty($orderItem['store'])) {
                        throw new \Exception('请选择自提门店。');
                    }
                    $order->store_id = $orderItem['store']['id'];
                    $order->send_type = Order::SEND_TYPE_SELF;
                } elseif ($orderItem['delivery']['send_type'] === 'city') {
                    $order->distance = $orderItem['distance'];
                    $order->location = $data['user_address']['longitude'] . ',' . $data['user_address']['latitude'];
                    $order->send_type = Order::SEND_TYPE_CITY;
                    $order->store_id = 0;
                } else {
                    $order->send_type = Order::SEND_TYPE_EXPRESS;
                    $order->store_id = 0;
                }
                \Yii::warning('order_submit foreach 7');
                $order->sign = $this->sign !== null ? $this->sign : '';
                $order->token = $this->token;
                $order->status = $this->status;
                \Yii::warning('order_submit order:' . json_encode($order));
                if (!$order->save()) {
                    throw new \Exception((new BaseModel())->responseErrorMsg($order));
                }
                \Yii::warning('order_submit foreach 8');
                if ($orderItem['mch']['id'] > 0) {
                    $mchOrder = new MchOrder();
                    $mchOrder->order_id = $order->id;
                    $res = $mchOrder->save();
                    if (!$res) {
                        throw new \Exception('多商户订单创建失败');
                    }
                }

                //添加公共订单
//                $commonOrderId = $form->extraCommonOrder($order,$orderItem);
//                if(!$commonOrderId){
//                    throw new \Exception('添加公共订单失败');
//                }

                \Yii::warning('order_submit foreach 7-1 goods_list=' . var_export($orderItem["goods_list"], true));
                foreach ($orderItem['goods_list'] as $goodsItem) {
                    $form->subGoodsNum($goodsItem['goods_attr'], $goodsItem['num'], $goodsItem);
                    $form->extraOrderDetail($order, $goodsItem);
                }

                // 优惠券标记已使用(此段代码没有用)
                if ($order->use_user_coupon_id) {
                    $userCoupon = UserCoupon::findOne($order->use_user_coupon_id);
                    $userCoupon->is_use = 1;
                    $userCoupon->is_failure = 1;
                    if ($userCoupon->update(true, ['is_use']) === false) {
                        throw new \Exception('优惠券状态更新失败。');
                    }
                }
                \Yii::warning('order_submit coupon');
                // 扣除积分
                if ($order->use_score) {
                    if (!\Yii::$app->currency->setUser($user)->score->sub($order->use_score, '下单积分抵扣')) {
                        throw new \Exception('积分操作失败。');
                    }
                }
                \Yii::warning('order_submit use_score');

                /**
                 * 开放额外的订单处理接口
                 */
                $form->extraOrder($order, $orderItem);

                // 购物车ID
                $cartIds = [];
                foreach ($orderItem['form_data']['goods_list'] as $goodsItem) {
                    if (isset($goodsItem["cart_id"]) && $goodsItem["cart_id"] > 0) {
                        $cartIds[] = $goodsItem['cart_id'];
                    }
                }

                \Yii::warning('order_submit cart');
                $event = new OrderEvent();
                $event->order = $order;
                $event->sender = $this;
                $event->cartIds = $cartIds;
                $event->pluginData = ['sign' => 'vip_card', 'vip_discount' => $orderItem['vip_discount'] ?? null];
                \Yii::$app->trigger(Order::EVENT_CREATED, $event);

                $commonOrderJobs[] = $order;

                \Yii::warning('order_submit commonOrderForm order='.var_export($order,true));
            }

            \Yii::warning('order_submit commit');
            $t->commit();
            //添加公共订单任务
            \Yii::warning('----------------------------公共订单任务-------------------------------------------');
            $commonOrderForm = new CommonOrderForm();
            $commonOrderForm->batchCommonOrderJob($commonOrderJobs, CommonOrderDetail::STATUS_NORMAL, CommonOrderDetail::TYPE_MALL_GOODS);
        } catch (\Exception $e) {
            $t->rollBack();
            \Yii::error("file:" . $e->getFile() . ";Line:" . $e->getLine() . ";message:" . $e->getMessage());
            $orderSubmitResult = new OrderSubmitResult();
            $orderSubmitResult->token = $this->token;
            $orderSubmitResult->data = $e->getFile() . ";Line:" . $e->getLine() . ";message:" . $e->getMessage();
            $orderSubmitResult->save();
            throw $e;
        }
    }
}
