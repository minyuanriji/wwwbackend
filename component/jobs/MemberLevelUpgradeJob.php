<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * Created by PhpStorm
 * Author: vita
 * Date: 2020-12-23
 * Time: 09:38
 */

namespace app\component\jobs;


use app\helpers\SerializeHelper;
use app\logic\CommonLogic;
use app\models\CommonOrder;
use app\models\CommonOrderDetail;
use app\forms\common\member\CommonMemberLevel;
use app\models\Goods;
use app\models\Order;
use app\models\PriceLog;
use app\models\User;
use app\models\UserChildren;
use app\models\UserGrowth;
use app\models\MemberLevel;
use yii\base\Component;
use yii\queue\JobInterface;
use yii\queue\Queue;

class MemberLevelUpgradeJob extends Component implements JobInterface
{
    public $user_id;
    public $mall_id;

    /**
     * @param Queue $queue which pushed and is handling the job
     * @return void|mixed result of the job execution
     */
    public function execute($queue)
    {
        // TODO: Implement execute() method.
        \Yii::warning("MemberLevelUpgradeJob ---会员升级队列开始---".$this->mall_id."--".$this->user_id);
        // $user = User::findOne($this->user_id);
        // if (!$user) {
        //     return;
        // }
        $commonMallMember = new CommonMemberLevel();
        $member = User::findOne(['mall_id' => $this->mall_id, 'id' => $this->user_id, 'is_delete' => 0]);
        $orderMoneyCount = $commonMallMember->getOrderMoneyCount($this->mall_id, $this->user_id);
        \Yii::warning("MemberLevelUpgradeJob user=".var_export($member,true));

        if (!$member) {
            \Yii::warning('用户不存在');
            return;
        }
        \Yii::warning("MemberLevelUpgradeJob execute");
        try{
            if ($member) {
                $level_list = MemberLevel::find()->where(['mall_id' => $this->mall_id, 'status' => 1, 'is_delete' =>0, 'auto_update'=>1])->andWhere(['>', 'level', $member->level])->orderBy('level desc')->all();
                \Yii::warning("MemberLevelUpgradeJob execute level_list=".var_export($level_list,true));
                if ($level_list) {
                    $flag = false;
                    $level_value = 0;
                    $upgrade_status = 0;
                    /**
                     * @var MemberLevel $level_list []
                     * @var MemberLevel $level
                     */
                    foreach ($level_list as $level) {
                        $level_value = $level->level;
                        $common_order_upgrade_status = $level->buy_compute_way;
                        \Yii::warning("MemberLevelUpgradeJob level_list level_value={$level_value};upgrade_type_goods=".$level->upgrade_type_goods);
                        if ($level->upgrade_type_goods) {
                            $upgrade_status = User::UPGRADE_STATUS_GOODS;
                            \Yii::warning("MemberLevelUpgradeJob level_list level->goods_type=".$level->goods_type);
                            if ($level->goods_type == 1) {//任意商品
                                if ($common_order_upgrade_status == CommonOrder::STATUS_IS_PAY) {
                                    $order = CommonOrder::find()->where(['is_delete' => 0, 'mall_id' => $this->mall_id, 'user_id' => $this->user_id])->andWhere(['is_pay' => CommonOrder::STATUS_IS_PAY])->exists();
                                } else {
                                    $order = CommonOrder::find()->where(['is_delete' => 0, 'mall_id' => $this->mall_id, 'user_id' => $this->user_id])->andWhere(['status' => CommonOrder::STATUS_COMPLETE])->exists();
                                }
                                //$order = CommonOrder::find()->where(['user_id' => $this->user_id, 'mall_id' => $this->mall_id, 'is_delete' => 0, 'status' => $common_order_upgrade_status])->exists();
                                if ($order) {
                                    $flag = true;
                                    break;
                                }
                            } elseif ($level->goods_type == 2) {
                                $goods_warehouse_ids = SerializeHelper::decode($level->goods_warehouse_ids);
                                \Yii::warning("MemberLevelUpgradeJob level_list goods_warehouse_ids=".var_export($goods_warehouse_ids,true));
                                if (!empty($goods_warehouse_ids)) {
                                    $goods_ids = Goods::find()->where(['mall_id' => $this->mall_id, 'is_delete' => 0])->andWhere(['goods_warehouse_id' => $goods_warehouse_ids])->select('id')->column();
                                    \Yii::warning("MemberLevelUpgradeJob level_list goods_ids=".var_export($goods_ids,true));
                                    if (!empty($goods_ids)) {
                                        $isGoods = null;

                                        if ($common_order_upgrade_status == CommonOrder::STATUS_IS_PAY) {
                                            $isGoods = CommonOrder::find()->alias('co')
                                                ->leftJoin(['cod' => CommonOrderDetail::tableName()], 'cod.common_order_id=co.id')
                                                ->andWhere(['co.is_pay' =>  CommonOrder::STATUS_IS_PAY])
                                                ->andWhere(['cod.user_id' => $this->user_id, 'cod.goods_type' => CommonOrderDetail::TYPE_MALL_GOODS])
                                                ->andWhere(['cod.goods_id' => $goods_ids])
                                                ->exists();
                                        }
                                        if ($common_order_upgrade_status == 2) {
                                            $isGoods = CommonOrder::find()->alias('co')
                                                ->leftJoin(['cod' => CommonOrderDetail::tableName()], 'cod.common_order_id=co.id')
                                                ->andWhere(['co.status' => CommonOrder::STATUS_IS_COMPLETE])
                                                ->andWhere(['cod.user_id' => $this->user_id, 'cod.goods_type' => CommonOrderDetail::TYPE_MALL_GOODS])
                                                ->andWhere(['cod.goods_id' => $goods_ids])
                                                ->exists();
                                        }

                                        if ($isGoods) {
                                            $flag = true;
                                            break;
                                        }
                                    }
                                }
                            }
                        }
                        \Yii::warning("MemberLevelUpgradeJob level_list level->upgrade_type_condition=".$level->upgrade_type_condition);
                        if ($level->upgrade_type_condition) { //使用条件升级
                            $upgrade_status = User::UPGRADE_STATUS_CONDITION;
                            
                            \Yii::warning('会员('.$userId.')升级，当前用户消费总额' . $orderMoneyCount);
                            $nowLevel = $this->user->level;

                            //查询按订单金额能直接升到的最高等级
                            $nextLevels = null;
                            if($level->money <= $orderMoneyCount){
                                $nextLevels = $level_value ?? null;
                            }

                            //按商品支付金额能直接升到的最高等级
                            $levelByPurchaseMoney = null;
                            if($is_purchase ==1 && $level->price <= $this->order->total_goods_original_price){
                                $levelByPurchaseMoney = $level_value ?? null;
                            }

                            //确定用户最终能升到哪个级别
                            if(!empty($nextLevels) && !empty($levelByPurchaseMoney)){
                                //同时有满足订单累计和单品支付的情况，判断那个级别大就升级哪个
                                $nextLevels = $nextLevels > $levelByPurchaseMoney ? $nextLevels : $levelByPurchaseMoney;
                            }else{
                                //有任意一种升级条件不满足
                                $nextLevels = !empty($nextLevels) ? $nextLevels : ($levelByPurchaseMoney ?? null);
                            }
                            \Yii::warning("BaseOrderSalesHandler upLevel nextLevels=".var_export($nextLevels,true));

                            if($nextLevels){
                                $level_value = $nextLevels;
                                $flag = true;
                                break;
                            }
                        }
                        \Yii::warning("MemberLevelUpgradeJob level_list foreach");
                    }
                    \Yii::warning("MemberLevelUpgradeJob flag=".$flag.";level_value={$level_value}");
                    if ($flag) {
                        \Yii::warning("用户：{$this->user_id}，会员升级成功");
                        $member->level = $level_value;
                        $member->upgrade_status = $upgrade_status;
                        $member->upgrade_time = time();
                        $member->save();
                    } else {
                        \Yii::warning("用户：{$this->user_id}，会员升级失败");
                    }
                }
            }
        }catch(\Exception $ex){
            \Yii::error("MemberLevelUpgradeJob execute excetion errorMsg=".CommonLogic::getExceptionMessage($ex));
        }

    }
}