<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * Created by PhpStorm
 * Author: ganxiaohao
 * Date: 2020-05-22
 * Time: 14:21
 */

namespace app\plugins\agent\jobs;


use app\helpers\SerializeHelper;
use app\logic\IntegralLogic;
use app\models\CommonOrder;
use app\models\CommonOrderDetail;
use app\models\Goods;
use app\models\Mall;
use app\models\UserChildren;
use app\models\UserGrowth;
use app\models\UserParent;
use app\plugins\agent\models\Agent;
use app\plugins\agent\models\AgentLevel;
use app\plugins\agent\models\AgentLevelNum;
use app\plugins\agent\models\AgentSetting;
use Yii;
use yii\base\Component;
use yii\queue\JobInterface;
use yii\queue\Queue;

class AgentLevelUpgradeJob extends Component implements JobInterface
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

        \Yii::warning("************************用户：{$this->user_id}经销商升级队列开始执行******************************");

        $is_enable = AgentSetting::getValueByKey(AgentSetting::IS_ENABLE, $this->mall_id);
        if (!$is_enable) {
            \Yii::warning('系统没有开启经销商提成');
            return;
        }
        $is_contain_self = AgentSetting::getValueByKey(AgentSetting::IS_CONTAIN_SELF, $this->mall_id);
        if (!$is_contain_self) {
            $is_contain_self = 0;
        }
        $agent = Agent::findOne(['mall_id' => $this->mall_id, 'user_id' => $this->user_id, 'is_delete' => 0]);
        \Yii::warning("AgentLevelUpgradeJob execute");
        $mall = Mall::findOne(['id' => $this->mall_id]);
        Yii::$app->mall = $mall;
        $update_level_at=0;


        if (!$agent) {
            $agent = new Agent();
            $agent->mall_id = $this->mall_id;
            $agent->user_id = $this->user_id;
            $agent->level = -1;
            $agent->upgrade_level_at=0;

        } else {
            $update_level_at=$agent->upgrade_level_at;
            \Yii::warning('当前的经销商level' . $agent->level);
        }
        $level_list = AgentLevel::find()->where(['mall_id' => $this->mall_id, 'is_use' => 1])->andWhere(['>', 'level', $agent->level])->orderBy('level desc')->all();
        \Yii::warning("AgentLevelUpgradeJob execute");
        if ($level_list) {
            $flag = false;
            $level_value = 0;
            $upgrade_status = 0;
            /**
             * @var AgentLevel $level_list []
             * @var AgentLevel $level
             */
            \Yii::warning('找到可以升级的等级');
            foreach ($level_list as $level) {
                \Yii::warning('当前的等级为：' . $level->level);
                $level_value = $level->level;
                if ($level->upgrade_type_goods) {
                    \Yii::warning('按照商品条件----------------------------------');
                    $upgrade_status = Agent::UPGRADE_STATUS_GOODS;
                    if ($level->goods_type == 1) {//任意商品
                        $buy_goods_type = $level->buy_goods_type;
                        if($buy_goods_type==1){
                            $order = CommonOrder::find()->where(['user_id' => $this->user_id, 'mall_id' => $this->mall_id, 'is_delete' => 0, 'is_pay' => CommonOrder::STATUS_IS_PAY])->andWhere(['>','created_at',$update_level_at])->exists();
                        }else{
                            $order = CommonOrder::find()->where(['user_id' => $this->user_id, 'mall_id' => $this->mall_id, 'is_delete' => 0, 'status' => CommonOrder::STATUS_IS_COMPLETE])->andWhere(['>','created_at',$update_level_at])->exists();
                        }

                       if ($order) {
                            $flag = true;
                            break;
                        }
                    } elseif ($level->goods_type == 2) {
                        $goods_warehouse_ids = SerializeHelper::decode($level->goods_warehouse_ids);
                        //这里的状态有问题，需要维护CommonOrder 表的status

                        \Yii::warning("商品的goods_warehouse_id" . var_export($goods_warehouse_ids, true));
                        if (!empty($goods_warehouse_ids)) { //选定的商品
                            $goods_ids = Goods::find()->where(['mall_id' => $this->mall_id, 'is_delete' => 0])->andWhere(['goods_warehouse_id' => $goods_warehouse_ids])->select('id')->column();
                            \Yii::warning("商品的goods_id" . var_export($goods_ids, true));
                            if (!empty($goods_ids)) {
                                $isGoods = false;
                                $buy_goods_type = $level->buy_goods_type;
                                if($buy_goods_type==1){
                                    $isGoods = CommonOrder::find()
                                        ->alias('co')
                                        ->leftJoin(['cod' => CommonOrderDetail::tableName()], 'cod.common_order_id=co.id')
                                        ->andWhere(['co.user_id' => $this->user_id, 'co.mall_id' => $this->mall_id, 'co.is_delete' => 0, 'co.is_pay' => 1])
                                        ->andWhere(['cod.goods_id' => $goods_ids])
                                        ->andWhere(['cod.goods_type' => CommonOrderDetail::TYPE_MALL_GOODS])
                                        ->andWhere(['>','co.created_at',$update_level_at])
                                        ->exists();
                                }else{
                                    $isGoods = CommonOrder::find()
                                        ->alias('co')
                                        ->leftJoin(['cod' => CommonOrderDetail::tableName()], 'cod.common_order_id=co.id')
                                        ->andWhere(['co.user_id' => $this->user_id, 'co.mall_id' => $this->mall_id, 'co.is_delete' => 0, 'co.status' => 1])
                                        ->andWhere(['cod.goods_id' => $goods_ids])
                                        ->andWhere(['cod.goods_type' => CommonOrderDetail::TYPE_MALL_GOODS])
                                        ->andWhere(['>','co.created_at',$update_level_at])
                                        ->exists();
                                }
                                \Yii::warning("AgentLevelUpgradeJob isGoods = " . $isGoods);
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
                    $upgrade_status = Agent::UPGRADE_STATUS_CONDITION;
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
                    \Yii::warning("AgentLevelUpgradeJob upgrade_type_condition checked_condition_keys=".var_export($checked_condition_keys,true));
                    \Yii::warning("AgentLevelUpgradeJob upgrade_type_condition checked_condition_values=".var_export($checked_condition_values,true));
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
                                    \Yii::warning('AgentLevelUpgradeJob 满足前面的条件');
                                    $team_price_count = $query2
                                        ->andWhere(['>=', 'ug.value', floatval($checked_condition_values[$key]['value']['val2'])])
                                        ->select('u.child_id')->count();
                                    if ($team_price_count >= floatval($checked_condition_values[$key]['value']['val3'])) {
                                        //完全满足条件
                                        \Yii::warning('AgentLevelUpgradeJob1 条件一完全满足');
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
                                        ->andWhere(['>','o.created_at',$update_level_at])
                                        ->andWhere(['o.goods_id' => $checked_condition_values[$key]['value']['val1']])->count();
                                    if ($buy_goods_count > $checked_condition_values[$key]['value']['val2']) {
                                        \Yii::warning('AgentLevelUpgradeJob1 经销商升级满足条件2');
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
                                        ->andWhere(['>','co.created_at',$update_level_at])
                                        ->sum('co.pay_price');

                                    \Yii::warning('AgentLevelUpgradeJob 团队业绩：' . $team_price);
                                    if ($is_contain_self) {
                                        $team_price += $self_price;
                                    }

                                    \Yii::warning('AgentLevelUpgradeJob1 团队业绩：' . $team_price);
                                    if ($team_price >= floatval($checked_condition_values[$key]['value']['val1'])) {
                                        \Yii::warning('AgentLevelUpgradeJob1 条件3达标！');
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
                                        ->andWhere(['>','co.created_at',$update_level_at])
                                        ->sum('co.pay_price');
                                    \Yii::warning('AgentLevelUpgradeJob1 2级团队订单总金额满：' . $team_price);
                                    if ($team_price && $team_price >= floatval($checked_condition_values[$key]['value']['val1'])) {
                                        $flag = true;
                                        break 2;
                                    }
                                }

                            }
                        }
                    }


                    \Yii::warning('AgentLevelUpgradeJob key' . var_export($checked_condition_keys, true));
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
                                    \Yii::warning('AgentLevelUpgradeJob2 满足前面的条件');
                                    $team_price_count = $query2
                                        ->andWhere(['>=', 'ug.value', floatval($checked_condition_values[$key]['value']['val2'])])
                                        ->select('u.child_id')->count();
                                    if ($team_price_count >= floatval($checked_condition_values[$key]['value']['val3'])) {
                                        //完全满足条件
                                        \Yii::warning('AgentLevelUpgradeJob2 条件1达标');
                                        $flag = true;
                                    } else {

                                        \Yii::warning('AgentLevelUpgradeJob2 条件1不达标');
                                        break 1;
                                    }
                                } else {
                                    \Yii::warning('AgentLevelUpgradeJob2 条件1达标');
                                    break 1;
                                }
                            }
                            if ($key == 1) {
                                \Yii::warning('AgentLevelUpgradeJob2 条件2开始执行');

                                $query = UserChildren::find()->alias('u')->where(['u.user_id' => $this->user_id])
                                    ->where(['u.level' => 1]);
                                $query2 = clone $query;
                                $first_team_count = $query->count();
                                if ($first_team_count >= $checked_condition_values[$key]['value']['val']) {
                                    $buy_goods_count = $query2->leftJoin(['o' => CommonOrderDetail::tableName()], 'o.user_id=u.child_id')
                                        ->andWhere(['o.goods_type' => CommonOrderDetail::TYPE_MALL_GOODS])
                                        ->andWhere(['>','o.created_at',$update_level_at])
                                        ->andWhere(['o.goods_id' => $checked_condition_values[$key]['value']['val1']])->count();
                                    if ($buy_goods_count > $checked_condition_values[$key]['value']['val2']) {
                                        \Yii::warning('AgentLevelUpgradeJob2 条件2达标');
                                        $flag = true;
                                        continue;
                                    } else {
                                        \Yii::warning('AgentLevelUpgradeJob2 条件2不达标');
                                        break 1;
                                    }
                                } else {
                                    \Yii::warning('AgentLevelUpgradeJob2 条件2不达标');
                                    break 1;
                                }
                            }

                            \Yii::warning('$key==' . $key);
                            if ($key == 2) {
                                \Yii::warning('AgentLevelUpgradeJob2 条件3开始执行');
                                //个人业绩和团队业绩
                                $self_price = CommonOrder::find()->where(['user_id' => $this->user_id, 'status' => CommonOrder::STATUS_IS_COMPLETE, 'is_delete' => 0])->sum('pay_price');
                                if ($self_price && $self_price >= floatval($checked_condition_values[$key]['value']['val'])) { //个人业绩达标
                                    $team_price = UserChildren::find()->alias('uc')
                                        ->leftJoin(['co' => CommonOrder::tableName()], 'co.user_id=uc.child_id')
                                        ->where(['uc.user_id' => $this->user_id, 'co.status' => CommonOrder::STATUS_IS_COMPLETE, 'co.is_delete' => 0])
                                        ->andWhere(['>','co.created_at',$update_level_at])
                                        ->sum('co.pay_price');
                                    \Yii::warning('AgentLevelUpgradeJob2 团队业绩：' . $team_price);
                                    if ($is_contain_self) {
                                        $team_price += $self_price;
                                    }

                                    if ($team_price >= floatval($checked_condition_values[$key]['value']['val1'])) {
                                        \Yii::warning('AgentLevelUpgradeJob2 条件3达标！');
                                        $flag = true;
                                        continue;
                                    } else {
                                        \Yii::warning('AgentLevelUpgradeJob2 条件3不达标');
                                        break 1;
                                    }
                                } else {
                                    \Yii::warning('AgentLevelUpgradeJob2 条件3不达标');
                                    break 1;
                                }
                            }
                            if ($key == 3) {
                                \Yii::warning('AgentLevelUpgradeJob2 条件4开始执行');
                                //升级条件4
                                //2级客户数量   2级订单总金额
                                $team_count = UserChildren::find()->where(['user_id' => $this->user_id, 'is_delete' => 0, 'level' => 2])->count();
                                \Yii::warning('AgentLevelUpgradeJob2 2级团队人数达：' . $team_count);
                                if ($team_count >= floatval($checked_condition_values[$key]['value']['val'])) {
                                    $team_price = UserChildren::find()->alias('uc')
                                        ->leftJoin(['co' => CommonOrder::tableName()], 'co.user_id=uc.child_id')
                                        ->andWhere(['uc.user_id' => $this->user_id, 'co.is_delete' => 0, 'co.status' => CommonOrder::STATUS_IS_COMPLETE, 'uc.level' => 2])
                                        ->andWhere(['>','co.created_at',$update_level_at])
                                        ->sum('co.pay_price');
                                    if ($team_price && $team_price >= floatval($checked_condition_values[$key]['value']['val1'])) {
                                        $flag = true;
                                        \Yii::warning('AgentLevelUpgradeJob2 条件4达标');
                                        continue;
                                    } else {
                                        \Yii::warning('AgentLevelUpgradeJob2 条件4不达标');
                                        break 1;
                                    }
                                } else {
                                    \Yii::warning('AgentLevelUpgradeJob2 条件4不达标');
                                    break 1;
                                }
                            }
                        }
                        \Yii::warning('AgentLevelUpgradeJob2 当前的flag' . SerializeHelper::encode($flag));
                        if ($flag) {
                            break 1;
                        }
                    }
                }
            }

            if ($flag) {
                echo "用户：{$this->user_id}，经销商升级成功".PHP_EOL;
                $agent->level = $level_value;
                $agent->upgrade_status = $upgrade_status;
                $agent->upgrade_level_at = time();
                if (!$agent->save()) {
                    echo "用户：{$this->user_id}，经销商升级失败，发生错误：" . var_export($agent->getErrors(), true).PHP_EOL;
                } else {
                    #########################
                    # 经销商名额赠送
                    #########################
                    //自己升级送名额
                    $res = AgentLevelNum::increaseNum($agent,AgentLevelNum::SCENE_LEVELUP);
                    if($res === false) Yii::error("经销商升级送名额失败，msg:" . AgentLevelNum::getError());
                    $parent_id = UserParent::getParentIdByUser($agent['user_id']);
                    //上级推广送名额
                    $pagent = Agent::getAgentByUserId($parent_id);
                    if(!empty($pagent)){
                        echo '上级经销商推广名额赠送start'.PHP_EOL;
                        $res = AgentLevelNum::increaseNum($pagent,AgentLevelNum::SCENE_INVITED,$agent->level);
                        if($res === false) Yii::error("经销商推广送名额失败，msg:" . AgentLevelNum::getError());
                    }
                    
                    \Yii::warning("用户：{$this->user_id}，经销商升级成功！");

                    //经销商升级赠送购物券
                    $level_info = AgentLevel::find()->where(array('level'=>$agent->level))->asArray()->one();
                    IntegralLogic::levelupSendIntegral($level_info,$agent,1);
                    
                    return;
                }
            } else {
                \Yii::warning("用户：{$this->user_id}，经销商升级{$level->level}失败，条件不满足");
            }
        }
    }
}