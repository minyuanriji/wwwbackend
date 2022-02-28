<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 处理订单超过售后时间基础类
 * Author: zal
 * Date: 2020-05-18
 * Time: 11:10
 */

namespace app\handlers\orderHandler;

use app\helpers\SerializeHelper;
use app\events\OrderEvent;
use app\forms\common\member\CommonMemberLevel;
use app\logic\CommonLogic;
use app\logic\IntegralLogic;
use app\models\BaseModel;
use app\models\MemberLevel;
use app\models\Goods;
use app\models\Order;
use app\models\OrderDetail;
use app\models\OrderRefund;
use app\models\CommonOrder;
use app\models\CommonOrderDetail;
use app\models\User;
use app\plugins\mch\models\Mch;
use app\plugins\mch\models\MchAccountLog;
use app\plugins\mch\models\MchOrder;
use app\services\wechat\WechatTemplateService;
use yii\db\Exception;
use function foo\func;
use app\services\wechat\SendWechatTempService;


/**
 * @property User $user
 */
abstract class BaseOrderSalesHandler extends BaseOrderHandler
{
    /* @var Order $order */
    public $order;
    /* @var User $user */
    public $user;
    /* @var OrderDetail[] $orderDetailList */
    public $orderDetailList;

    public function handle()
    {
        $this->sales();
    }

    protected function sales()
    {
        /* @var OrderEvent $event */
        $event = $this->event;
        \Yii::$app->setMchId($event->order->mch_id);
        \Yii::warning('=============订单售后事件开始执行===========');

        try {
            $this->order = $event->order;
            
            $this->user = User::find()->where(['id' => $this->order->user_id])->one();

            $orderRefundList = OrderRefund::find()->where([
                'order_id' => $this->order->id,
                'is_delete' => 0
            ])->all();
            // 已退款的订单详情id列表
            $notOrderDetailIdList = [];
            if ($orderRefundList) {
                /* @var OrderRefund[] $orderRefundList */
                foreach ($orderRefundList as $orderRefund) {
                    if ($orderRefund->is_confirm == 0) {
                        return false;
                    } else {
                        if ($orderRefund->type == 1 && $orderRefund->status == 3) {
                            $notOrderDetailIdList[] = $orderRefund->order_detail_id;
                        }
                    }
                }
            }
            $this->orderDetailList = OrderDetail::find()->where(['order_id' => $this->order->id, 'is_delete' => 0])
                ->with('goods')
                ->keyword(!empty($notOrderDetailIdList), ['not in', 'id', $notOrderDetailIdList])->all();

            $this->action();
        } catch (\Exception $e) {
            \Yii::error($e);
        }
    }

    protected function action()
    {
        // 发放积分
        // $this->giveIntegral();
        // 发放积分券
        // echo '完成后发放积分券'.PHP_EOL;
        // IntegralLogic::shopSendScore($this->order);
        // 发放金豆券
        // echo '完成后发放金豆券'.PHP_EOL;
        // IntegralLogic::shopSendIntegral($this->order);
        // 消费升级会员等级
        //echo '消费升级会员等级'.PHP_EOL;
        //$this->upLevel();
    }

    /**
     * 积分发放
     * @return bool
     */
    protected function giveIntegral()
    {
        try {
            $integral = 0;
            foreach ($this->orderDetailList as $orderDetail) {
                if($orderDetail->goods->enable_score){ //积分券开启跳过
                    continue;
                }

                $is_order_paid = $orderDetail->goods->is_order_paid;//商品订单设置支付状态
                $order_paid = $orderDetail->goods->order_paid ? SerializeHelper::decode($orderDetail->goods->order_paid) : [];//商品订单设置支付参数

                if($is_order_paid && $order_paid['is_score']){
                    continue;
                }else{
                    if (!in_array($orderDetail->refund_status, OrderDetail::ALLOW_ADD_SCORE_REFUND_STATUS)) {
                        continue;
                    }
        
                    if ($orderDetail->goods->give_score_type == 1) {
                        $integral += ($orderDetail->goods->give_score * $orderDetail->num);
                    } else {
                        $integral += (intval($orderDetail->goods->give_score * $orderDetail->total_price / 100));
                    }
                }
            }
            if ($integral > 0) {
                \Yii::$app->currency->setUser($this->user)->score->add($integral, '订单购买赠送积分');
            }
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * 商户结算
     * @param $res
     * @return bool
     * @throws \Exception
     */
    protected function transferToMch($res)
    {
        if (!$this->order->mch_id > 0) {
            return false;
        }
        $transaction = \Yii::$app->db->beginTransaction();
        try {
            $mch = Mch::findOne($this->order->mch_id);
            if (!$mch) {
                throw new \Exception('多端户结算:商户不存在');
            }
            /** @var OrderRefund[] $orderRefund */
            $orderRefund = OrderRefund::find()->where([
                'mall_id' => $this->order->mall_id,
                'mch_id' => $this->order->mch_id,
                'order_id' => $this->order->id
            ])->all();
            $totalPayPrice = $this->order->total_pay_price;
            if ($orderRefund) {
                foreach ($orderRefund as $rItem) {
                    if ($rItem->is_refund > 0) {
                        $totalPayPrice = $totalPayPrice - $rItem->reality_refund_price;
                    }
                }
            }
            $totalPayPrice = $totalPayPrice * (1 - $mch->transfer_rate / 1000);
            $totalPayPrice = $totalPayPrice - $res['first_price'] - $res['second_price'] - $res['third_price'];

            $mch->account_money += $totalPayPrice;
            $res = $mch->save();
            if (!$res) {
                throw new \Exception((new BaseModel())->responseErrorMsg($mch));
            }

            $mchOrder = MchOrder::findOne(['order_id' => $this->order->id]);
            if (!$mchOrder) {
                throw new \Exception('多端户结算:多商户订单不存在');
            }
            $mchOrder->is_transfer = 1;
            $res = $mchOrder->save();
            if (!$res) {
                throw new \Exception((new BaseModel())->responseErrorMsg($mch));
            }

            $mchAccountLog = new MchAccountLog();
            $mchAccountLog->mall_id = $this->order->mall_id;
            $mchAccountLog->mch_id = $this->order->mch_id;
            $mchAccountLog->money = $totalPayPrice;
            $mchAccountLog->desc = '订单号:' . $this->order->order_no . '结算';
            $mchAccountLog->type = 1;
            $res = $mchAccountLog->save();
            if (!$res) {
                throw new \Exception((new BaseModel())->responseErrorMsg($mch));
            }

            $transaction->commit();
            return true;
        } catch (Exception $e) {
            $transaction->rollBack();
            \Yii::error('商户结算异常:' . $e->getMessage());
            return false;
        }
    }

    /**
     * 会员升级
     * @return bool
     */
    // protected function upLevel()
    // {
    //     // 订单总额
    //     $commonMallMember = new CommonMemberLevel();
    //     $mallId = $this->order->mall_id;
    //     $userId = $this->order->user_id;
        
    //     $userDetail = User::findOne(['mall_id' => $mallId, 'id' => $userId, 'is_delete' => 0]);
    //     $orderMoneyCount = $commonMallMember->getOrderMoneyCount($mallId, $userId);
    //     try {
    //         if ($this->user) {
    //             $level_list = MemberLevel::find()->where(['mall_id' => $mallId, 'status' => 1, 'is_delete' => 0])->andWhere(['>', 'level', $this->user->level])->orderBy('level desc')->all();
    //             \Yii::warning("MemberLevelUpgradeJob execute level_list=".var_export($level_list,true));
    //             if ($level_list) {
    //                 $flag = false;
    //                 $level_value = 0;
    //                 $upgrade_status = 0;
    //                 /**
    //                  * @var MemberLevel $level_list []
    //                  * @var MemberLevel $level
    //                  */
    //                 foreach ($level_list as $level) {
    //                     $level_value = $level->level;
    //                     \Yii::warning("MemberLevelUpgradeJob level_list level_value={$level_value};upgrade_type_goods=".$level->upgrade_type_goods);
    //                     if ($level->upgrade_type_goods) {
    //                         $upgrade_status = User::UPGRADE_STATUS_GOODS;
    //                         \Yii::warning("MemberLevelUpgradeJob level_list level->goods_type=".$level->goods_type);
    //                         if ($level->goods_type == 1) {//任意商品
    //                             $order = CommonOrder::find()->where(['user_id' => $userId, 'mall_id' => $mallId, 'is_delete' => 0, 'status' => CommonOrder::STATUS_IS_COMPLETE])->exists();
    //                             if ($order) {
    //                                 $flag = true;
    //                                 break;
    //                             }
    //                         } elseif ($level->goods_type == 2) {
    //                             $goods_warehouse_ids = SerializeHelper::decode($level->goods_warehouse_ids);
    //                             \Yii::warning("MemberLevelUpgradeJob level_list goods_warehouse_ids=".var_export($goods_warehouse_ids,true));
    //                             if (!empty($goods_warehouse_ids)) {
    //                                 $goods_ids = Goods::find()->where(['mall_id' => $mallId, 'is_delete' => 0])->andWhere(['goods_warehouse_id' => $goods_warehouse_ids])->select('id')->column();
    //                                 //\Yii::warning("MemberLevelUpgradeJob level_list goods_ids=".var_export($goods_ids,true).'TYPE_MALL_GOODS='.CommonOrderDetail::TYPE_MALL_GOODS);
    //                                 if (!empty($goods_ids)) {
    //                                     $isGoods = CommonOrder::find()
    //                                         ->alias('co')
    //                                         ->leftJoin(['cod' => CommonOrderDetail::tableName()], 'cod.common_order_id=co.id')
    //                                         ->andWhere(['co.user_id' => $userId, 'co.mall_id' => $mallId, 'co.is_delete' => 0, 'co.status' => CommonOrder::STATUS_IS_COMPLETE])
    //                                         ->andWhere(['cod.goods_id' => $goods_ids])
    //                                         ->andWhere(['cod.goods_type' => CommonOrderDetail::TYPE_MALL_GOODS])
    //                                         ->exists();
    //                                         //\Yii::warning("MemberLevelUpgradeJob isGoods=".var_export($isGoods,true));
    //                                     if ($isGoods) {
    //                                         $flag = true;
    //                                         break;
    //                                     }
    //                                 }
    //                             }
    //                         }
    //                     }
    //                     \Yii::warning("MemberLevelUpgradeJob level_list level->upgrade_type_condition=".$level->upgrade_type_condition);
    //                     if ($level->upgrade_type_condition) { //使用条件升级
    //                         $upgrade_status = User::UPGRADE_STATUS_CONDITION;
                            
    //                         \Yii::warning('会员('.$userId.')升级，当前用户消费总额' . $orderMoneyCount);
    //                         $nowLevel = $this->user->level;

    //                         //查询按订单金额能直接升到的最高等级
    //                         $nextLevels = null;
    //                         if($level->money <= $orderMoneyCount){
    //                             $nextLevels = $level_value ?? null;
    //                         }
    //                         // $levelByOrderMoney = MemberLevel::find()
    //                         //     ->where(['mall_id' => 5, 'is_delete' => 0, 'status' => 1,'auto_update'=>1])
    //                         //     ->andWhere(['>', 'level', $nowLevel])
    //                         //     ->andWhere(['<=', 'money', $orderMoneyCount])
    //                         //     ->orderBy(['level' => SORT_DESC])
    //                         //     ->one();

    //                         //按商品支付金额能直接升到的最高等级
    //                         $levelByPurchaseMoney = null;
    //                         if($is_purchase ==1 && $level->price <= $this->order->total_goods_original_price){
    //                             $levelByPurchaseMoney = $level_value ?? null;
    //                         }
    //                         // $levelByPurchaseMoney = MemberLevel::find()
    //                         //     ->where(['mall_id' => 5, 'is_delete' => 0, 'status' => 1,'is_purchase'=>1])
    //                         //     ->andWhere(['>', 'level', $nowLevel])
    //                         //     ->andWhere(['<=', 'price', $this->order->total_goods_original_price])
    //                         //     ->orderBy(['level' => SORT_DESC])
    //                         //     ->one();

    //                         //确定用户最终能升到哪个级别
    //                         if(!empty($nextLevels) && !empty($levelByPurchaseMoney)){
    //                             //同时有满足订单累计和单品支付的情况，判断那个级别大就升级哪个
    //                             $nextLevels = $nextLevels > $levelByPurchaseMoney ? $nextLevels : $levelByPurchaseMoney;
    //                         }else{
    //                             //有任意一种升级条件不满足
    //                             $nextLevels = !empty($nextLevels) ? $nextLevels : ($levelByPurchaseMoney ?? null);
    //                         }
    //                         \Yii::warning("BaseOrderSalesHandler upLevel nextLevels=".var_export($nextLevels,true));

    //                         if($nextLevels){
    //                             $level_value = $nextLevels;
    //                             $flag = true;
    //                             break;
    //                             // $this->user->level = $nextLevels;
    //                             // if (!$this->user->save()) {
    //                             //     throw new Exception($this->user->errors[0]);
    //                             // }
    //                         }
    //                     }
    //                     \Yii::warning("MemberLevelUpgradeJob level_list foreach");
    //                 }
    //                 \Yii::warning("MemberLevelUpgradeJob flag=".$flag.";level_value={$level_value}");
    //                 if ($flag) {
    //                     \Yii::warning("用户：{$userId}，会员升级成功");
    //                     $userDetail->level = $level_value;
    //                     $userDetail->upgrade_status = $upgrade_status;
    //                     $userDetail->upgrade_time = time();
    //                     $userDetail->save();
    //                     //微信模板消息
    //                     SendWechatTempService::sendUplevel($userId);

    //                     return true;
    //                 } else {
    //                     \Yii::warning("用户：{$userId}，会员升级失败");
    //                 }
    //             }
    //         }
    //     } catch (Exception $e) {
    //         \Yii::error('会员('.$this->order->user_id.')升级失败，error=' . $e->getFile().";Line:".$e->getLine().";message:".$e->getMessage());
    //         return false;
    //     }
    // }
    // protected function upLevel()
    // {
    //     try {
    //         // 订单总额
    //         $commonMallMember = new CommonMemberLevel();
    //         $mallId = $this->order->mall_id;
    //         $userId = $this->order->user_id;
    //         $orderMoneyCount = $commonMallMember->getOrderMoneyCount($mallId, $userId);
    //         \Yii::warning('会员('.$this->order->user_id.')升级，当前用户消费总额' . $orderMoneyCount);
    //         $nowLevel = $this->user->level;

    //         //查询按订单金额能直接升到的最高等级
    //         $nextLevels = null;
    //         $levelByOrderMoney = MemberLevel::find()
    //             ->where(['mall_id' => 5, 'is_delete' => 0, 'status' => 1,'auto_update'=>1])
    //             ->andWhere(['>', 'level', $nowLevel])
    //             ->andWhere(['<=', 'money', $orderMoneyCount])
    //             ->orderBy(['level' => SORT_DESC])
    //             ->one();
    //         $nextLevels = $levelByOrderMoney ?? null;
    //         //按商品支付金额能直接升到的最高等级
    //         $levelByPurchaseMoney = MemberLevel::find()
    //             ->where(['mall_id' => 5, 'is_delete' => 0, 'status' => 1,'is_purchase'=>1])
    //             ->andWhere(['>', 'level', $nowLevel])
    //             ->andWhere(['<=', 'price', $this->order->total_goods_original_price])
    //             ->orderBy(['level' => SORT_DESC])
    //             ->one();

    //         //确定用户最终能升到哪个级别
    //         if(!empty($nextLevels) && !empty($levelByPurchaseMoney)){
    //             //同时有满足订单累计和单品支付的情况，判断那个级别大就升级哪个
    //             $nextLevels = $nextLevels->level > $levelByPurchaseMoney->level ? $nextLevels : $levelByPurchaseMoney;
    //         }else{
    //             //有任意一种升级条件不满足
    //             $nextLevels = !empty($nextLevels) ? $nextLevels : ($levelByPurchaseMoney ?? null);
    //         }
    //         \Yii::warning("BaseOrderSalesHandler upLevel nextLevels=".var_export($nextLevels,true));

    //         if($nextLevels){
    //             $this->user->level = $nextLevels->level;
    //             if (!$this->user->save()) {
    //                 throw new Exception($this->user->errors[0]);
    //             }
    //         }
    //         //微信模板消息
    //         SendWechatTempService::sendUplevel($userId);

    //         return true;
    //     } catch (Exception $e) {
    //         \Yii::error('会员('.$this->order->user_id.')升级失败，error=' . $e->getFile().";Line:".$e->getLine().";message:".$e->getMessage());
    //         return false;
    //     }
    // }

}
