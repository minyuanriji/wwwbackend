<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * Created by PhpStorm
 * Author: ganxiaohao
 * Date: 2020-05-22
 * Time: 14:21
 */

namespace app\plugins\boss\jobs;


use app\helpers\SerializeHelper;
use app\models\CommonOrder;
use app\models\CommonOrderDetail;
use app\models\Goods;
use app\models\UserChildren;
use app\models\UserGrowth;
use app\plugins\boss\models\Boss;
use app\plugins\boss\models\BossLevel;
use app\plugins\boss\models\BossSetting;
use yii\base\Component;
use yii\queue\JobInterface;
use yii\queue\Queue;

class BossLevelUpgradeJob extends Component implements JobInterface
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

        \Yii::warning('======================================================');
        \Yii::warning('股东分红升级队列开始执行');
        \Yii::warning("************************用户：{$this->user_id}升级队列开始执行******************************");
        $is_enable = BossSetting::getValueByKey(BossSetting::IS_ENABLE, $this->mall_id);
        if (!$is_enable) {
            \Yii::warning('系统没有开启股东提成');
            return;
        }
        $boss = Boss::findOne(['mall_id' => $this->mall_id, 'user_id' => $this->user_id, 'is_delete' => 0]);
        \Yii::warning("BossLevelUpgradeJob execute");
        if (!$boss) {
            $boss = new Boss();
            $boss->mall_id = $this->mall_id;
            $boss->user_id = $this->user_id;
            $boss->level = -1;
        } else {
            \Yii::warning('当前的股东level' . $boss->level);
        }
        
     
        $level_list = BossLevel::find()->where(['mall_id' => $this->mall_id, 'is_enable' => 1,'is_delete'=>0
        ])->andWhere(['>', 'level', $boss->level])->orderBy('level desc')->all();
        \Yii::warning("BossLevelUpgradeJob execute");
        if ($level_list) {
            $flag = false;
            $level_value = 0;
            $upgrade_status = 0;
            /**
             * @var BossLevel $level_list []
             * @var BossLevel $level
             */
            \Yii::warning('找到可以升级的等级');
            foreach ($level_list as $level) {
                \Yii::warning('当前的等级为：' . $level->level);
                $level_value = $level->level;
                if ($level->upgrade_type_goods) {
                    \Yii::warning('按照商品条件----------------------------------');
                    $upgrade_status = Boss::UPGRADE_STATUS_GOODS;
                    if ($level->goods_type == 1) {  //任意商品
                        $buy_goods_type = $level->buy_goods_type;
                        if($buy_goods_type==1){
                            $order = CommonOrder::find()->where(['user_id' => $this->user_id, 'mall_id' => $this->mall_id, 'is_delete' => 0, 'is_pay' => CommonOrder::STATUS_IS_PAY])->exists();
                        }else{
                            $order = CommonOrder::find()->where(['user_id' => $this->user_id, 'mall_id' => $this->mall_id, 'is_delete' => 0, 'status' => CommonOrder::STATUS_IS_COMPLETE])->exists();
                        }
                       if ($order) {
                            $flag = true;
                            break;
                        }
                    } elseif ($level->goods_type == 2) { //指定产品
                        $goods_warehouse_ids = [];
                        if($level->goods_warehouse_ids != "\"\"" && !empty($level->goods_warehouse_ids)){
                            $goods_warehouse_ids = SerializeHelper::decode($level->goods_warehouse_ids);
                        }
                        //这里的状态有问题，需要维护CommonOrder 表的status
                        \Yii::warning("商品的goods_warehouse_id" . var_export($goods_warehouse_ids, true));
                        if (!empty($goods_warehouse_ids)) { //选定的商品
                            $goods_ids = Goods::find()->where(['mall_id' => $this->mall_id, 'is_delete' => 0])->andWhere(['goods_warehouse_id' => $goods_warehouse_ids])->select('id')->column();
                            \Yii::warning("商品的goods_id" . var_export($goods_ids, true));
                            if (count($goods_ids)) {
                                $isGoods = false;
                                //购买商品，升级方式 1.支付完成 2.订单完成
                                $buy_goods_type = $level->buy_goods_type;
                                if($buy_goods_type == 1){
                                    $isGoods = CommonOrder::find()
                                        ->alias('co')
                                        ->leftJoin(['cod' => CommonOrderDetail::tableName()], 'cod.common_order_id=co.id')
                                        ->andWhere(['co.user_id' => $this->user_id, 'co.mall_id' => $this->mall_id, 'co.is_delete' => 0, 'co.is_pay' => 1])
                                        ->andWhere(['cod.goods_id' => $goods_ids])
                                        ->andWhere(['cod.goods_type' => CommonOrderDetail::TYPE_MALL_GOODS])
                                        ->exists();
                                }else{
                                    $isGoods = CommonOrder::find()
                                        ->alias('co')
                                        ->leftJoin(['cod' => CommonOrderDetail::tableName()], 'cod.common_order_id=co.id')
                                        ->andWhere(['co.user_id' => $this->user_id, 'co.mall_id' => $this->mall_id, 'co.is_delete' => 0, 'co.status' => 1])
                                        ->andWhere(['cod.goods_id' => $goods_ids])
                                        ->andWhere(['cod.goods_type' => CommonOrderDetail::TYPE_MALL_GOODS])
                                        ->exists();
                                }
                                if ($isGoods) {
                                    \Yii::warning('按照商品升级满足条件----------------------------------');
                                    $flag = true;
                                    break;
                                }
                            }
                        }
                    }
                }
                if ($level->upgrade_type_condition) { //使用条件升级
                    $upgrade_status = Boss::UPGRADE_STATUS_CONDITION;
                    if (!$level->condition_type) {
                        break;
                    }
                    $checked_condition_values = $level->checked_condition_values;
                    $checked_condition_keys = $level->checked_condition_keys;
                    if (!$checked_condition_keys || !$checked_condition_values) {
                        break;
                    }
                    if ($checked_condition_keys == "" || $checked_condition_values == "") {
                        break;
                    }
                    $checked_condition_values = SerializeHelper::decode($checked_condition_values);
                    $checked_condition_keys = SerializeHelper::decode($checked_condition_keys);
                    //满足任意条件
                    if ($level->condition_type == 1) {
                        foreach ($checked_condition_keys as $key) {
                            if ($key == 0) {  //第一个条件
                                $query = UserChildren::find()->alias('u')->where(['u.user_id' => $this->user_id])
                                    ->leftJoin(['ug' => UserGrowth::tableName()], 'ug.user_id=u.child_id')
                                    ->andWhere(['ug.keyword' => UserGrowth::KEY_SELF_BUY_ORDER_PRICE]);

                                $query2 = clone $query;
                                $first_price_count = $query
                                    ->andWhere(['u.level' => 1])
                                    ->andWhere(['>=', 'ug.value', floatval($checked_condition_values[$key]['value']['val'])])
                                    ->select('u.child_id')->count();
                                //一级下单满这么多的有几个人
                                if ($first_price_count >= floatval($checked_condition_values[$key]['value']['val1'])) {//满足前面的
                                    \Yii::warning('满足前面的条件');
                                    $team_price_count = $query2
                                        ->andWhere(['>=', 'ug.value', floatval($checked_condition_values[$key]['value']['val2'])])
                                        ->select('u.child_id')->count();
                                    if ($team_price_count >= floatval($checked_condition_values[$key]['value']['val3'])) {
                                        //完全满足条件
                                        \Yii::warning('条件一完全满足');
                                        $flag = true;
                                        break  2;
                                    }
                                }
                            }
                            if ($key == 1) {
                                //第二个条件

                                $query = UserChildren::find()->alias('u')->where(['u.user_id' => $this->user_id])
                                    ->where(['u.level' => 1]);
                                $query2 = clone $query;
                                $first_team_count = $query->count();
                                if ($first_team_count >= $checked_condition_values[$key]['value']['val']) {
                                    $buy_goods_count = $query2->leftJoin(['o' => CommonOrderDetail::tableName()], 'o.user_id=u.child_id')
                                        ->andWhere(['o.goods_type' => CommonOrderDetail::TYPE_MALL_GOODS])
                                        ->andWhere(['o.goods_id' => $checked_condition_values[$key]['value']['val1']])->count();
                                    if ($buy_goods_count > $checked_condition_values[$key]['value']['val2']) {
                                        \Yii::warning('股东升级满足条件2');
                                        $flag = true;
                                        break 2;
                                    }
                                }
                            }
                            if ($key == 2) {

                                //个人业绩和团队业绩
                                $self_price = CommonOrder::find()->where(['user_id' => $this->user_id, 'status' => CommonOrder::STATUS_IS_COMPLETE, 'is_delete' => 0])->sum('pay_price');
                                \Yii::warning('个人业绩：' . $self_price);
                                if ($self_price && $self_price >= floatval($checked_condition_values[$key]['value']['val'])) { //个人业绩达标
                                    $team_price = UserChildren::find()->alias('uc')
                                        ->leftJoin(['co' => CommonOrder::tableName()], 'co.user_id=uc.child_id')
                                        ->where(['uc.user_id' => $this->user_id, 'co.status' => CommonOrder::STATUS_IS_COMPLETE, 'co.is_delete' => 0])
                                        ->sum('co.pay_price');
                                    \Yii::warning('团队业绩：' . $team_price);
                                    if ($team_price >= floatval($checked_condition_values[$key]['value']['val1'])) {
                                        \Yii::warning('条件3达标！');
                                        $flag = true;
                                        break 2;
                                    }
                                }
                            }
                            if ($key == 3) {
                                //升级条件4

                                //2级客户数量   2级订单总金额

                                $team_count = UserChildren::find()->where(['user_id' => $this->user_id, 'is_delete' => 0, 'level' => 2])->count();
                                \Yii::warning('2级团队人数达：' . $team_count);
                                if ($team_count >= floatval($checked_condition_values[$key]['value']['val'])) {
                                    $team_price = UserChildren::find()->alias('uc')
                                        ->leftJoin(['co' => CommonOrder::tableName()], 'co.user_id=uc.child_id')
                                        ->andWhere(['uc.user_id' => $this->user_id, 'co.is_delete' => 0, 'co.status' => CommonOrder::STATUS_IS_COMPLETE, 'uc.level' => 2])
                                        ->sum('co.pay_price');
                                    \Yii::warning('2级团队订单总金额满：' . $team_price);
                                    if ($team_price && $team_price >= floatval($checked_condition_values[$key]['value']['val1'])) {
                                        $flag = true;
                                        break 2;
                                    }
                                }

                            }
                        }
                    }


                    \Yii::warning('key' . var_export($checked_condition_keys, true));
                    //满足全部
                    if ($level->condition_type == 2) {
                        foreach ($checked_condition_keys as $key) {

                            $flag = false;
                            \Yii::warning('------------------------------------');

                            \Yii::warning('------------------------------------key:' . $key);


                            if ($key == 0) {  //第一个条件
                                \Yii::warning('条件1开始执行');
                                $query = UserChildren::find()->alias('u')->where(['u.user_id' => $this->user_id])
                                    ->leftJoin(['ug' => UserGrowth::tableName()], 'ug.user_id=u.child_id')
                                    ->andWhere(['ug.keyword' => UserGrowth::KEY_SELF_BUY_ORDER_PRICE]);

                                $query2 = clone $query;
                                $first_price_count = $query
                                    ->andWhere(['u.level' => 1])
                                    ->andWhere(['>=', 'ug.value', floatval($checked_condition_values[$key]['value']['val'])])
                                    ->select('u.child_id')->count();
                                //一级下单满这么多的有几个人
                                if ($first_price_count >= floatval($checked_condition_values[$key]['value']['val1'])) {//满足前面的
                                    \Yii::warning('满足前面的条件');
                                    $team_price_count = $query2
                                        ->andWhere(['>=', 'ug.value', floatval($checked_condition_values[$key]['value']['val2'])])
                                        ->select('u.child_id')->count();
                                    if ($team_price_count >= floatval($checked_condition_values[$key]['value']['val3'])) {
                                        //完全满足条件
                                        \Yii::warning('条件1达标');
                                        $flag = true;
                                    } else {

                                        \Yii::warning('条件1不达标');
                                        break 1;
                                    }
                                } else {
                                    \Yii::warning('条件1达标');
                                    break 1;
                                }
                            }
                            if ($key == 1) {
                                \Yii::warning('条件2开始执行');

                                $query = UserChildren::find()->alias('u')->where(['u.user_id' => $this->user_id])
                                    ->where(['u.level' => 1]);
                                $query2 = clone $query;
                                $first_team_count = $query->count();
                                if ($first_team_count >= $checked_condition_values[$key]['value']['val']) {
                                    $buy_goods_count = $query2->leftJoin(['o' => CommonOrderDetail::tableName()], 'o.user_id=u.child_id')
                                        ->andWhere(['o.goods_type' => CommonOrderDetail::TYPE_MALL_GOODS])
                                        ->andWhere(['o.goods_id' => $checked_condition_values[$key]['value']['val1']])->count();
                                    if ($buy_goods_count > $checked_condition_values[$key]['value']['val2']) {
                                        \Yii::warning('条件2达标');
                                        $flag = true;
                                        continue;
                                    } else {
                                        \Yii::warning('条件2不达标');
                                        break 1;
                                    }
                                } else {
                                    \Yii::warning('条件2不达标');
                                    break 1;
                                }
                            }

                            \Yii::warning('$key==' . $key);
                            if ($key == 2) {
                                \Yii::warning('条件3开始执行');
                                //个人业绩和团队业绩
                                $self_price = CommonOrder::find()->where(['user_id' => $this->user_id, 'status' => CommonOrder::STATUS_IS_COMPLETE, 'is_delete' => 0])->sum('pay_price');
                                if ($self_price && $self_price >= floatval($checked_condition_values[$key]['value']['val'])) { //个人业绩达标
                                    $team_price = UserChildren::find()->alias('uc')
                                        ->leftJoin(['co' => CommonOrder::tableName()], 'co.user_id=uc.child_id')
                                        ->where(['uc.user_id' => $this->user_id, 'co.status' => CommonOrder::STATUS_IS_COMPLETE, 'co.is_delete' => 0])
                                        ->sum('co.pay_price');
                                    \Yii::warning('团队业绩：' . $team_price);

                                    if ($team_price >= floatval($checked_condition_values[$key]['value']['val1'])) {
                                        \Yii::warning('条件3达标！');
                                        $flag = true;
                                        continue;
                                    } else {
                                        \Yii::warning('条件3不达标');
                                        break 1;
                                    }
                                } else {
                                    \Yii::warning('条件3不达标');
                                    break 1;
                                }
                            }
                            if ($key == 3) {
                                \Yii::warning('条件4开始执行');
                                //升级条件4
                                //2级客户数量   2级订单总金额
                                $team_count = UserChildren::find()->where(['user_id' => $this->user_id, 'is_delete' => 0, 'level' => 2])->count();
                                \Yii::warning('2级团队人数达：' . $team_count);
                                if ($team_count >= floatval($checked_condition_values[$key]['value']['val'])) {
                                    $team_price = UserChildren::find()->alias('uc')
                                        ->leftJoin(['co' => CommonOrder::tableName()], 'co.user_id=uc.child_id')
                                        ->andWhere(['uc.user_id' => $this->user_id, 'co.is_delete' => 0, 'co.status' => CommonOrder::STATUS_IS_COMPLETE, 'uc.level' => 2])
                                        ->sum('co.pay_price');
                                    if ($team_price && $team_price >= floatval($checked_condition_values[$key]['value']['val1'])) {
                                        $flag = true;
                                        \Yii::warning('条件4达标');
                                        continue;
                                    } else {
                                        \Yii::warning('条件4不达标');
                                        break 1;
                                    }
                                } else {
                                    \Yii::warning('条件4不达标');
                                    break 1;
                                }
                            }
                        }
                        \Yii::warning('当前的flag' . SerializeHelper::encode($flag));
                        if ($flag) {
                            break 1;
                        }
                    }
                }
            }

            if ($flag) {
                \Yii::warning("用户：{$this->user_id}，股东升级成功");
                $boss->level = $level_value;
                $boss->upgrade_status = $upgrade_status;
                $boss->upgrade_level_at = time();
                if (!$boss->save()) {
                    \Yii::warning("用户：{$this->user_id}，股东升级失败，发生错误：" . var_export($boss->getErrors(), true));
                } else {
                    \Yii::warning("用户：{$this->user_id}，股东升级成功！");
                    return;
                }
            } else {
                \Yii::warning("用户：{$this->user_id}，股东升级{$level->level}失败，条件不满足");
            }
        }
    }
}