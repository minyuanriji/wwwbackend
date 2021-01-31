<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * Created by PhpStorm
 * Author: ganxiaohao
 * Date: 2020-05-22
 * Time: 14:21
 */

namespace app\plugins\distribution\jobs;


use app\helpers\SerializeHelper;
use app\logic\CommonLogic;
use app\models\CommonOrder;
use app\models\CommonOrderDetail;
use app\models\Goods;
use app\models\Order;
use app\models\PriceLog;
use app\models\User;
use app\models\UserChildren;
use app\models\UserGrowth;
use app\plugins\distribution\models\Distribution;
use app\plugins\distribution\models\DistributionLevel;
use app\plugins\distribution\models\DistributionSetting;
use yii\base\Component;
use yii\queue\JobInterface;
use yii\queue\Queue;

class DistributionLevelUpgradeJob extends Component implements JobInterface
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
        \Yii::warning("DistributionLevelUpgradeJob ---分销商升级队列开始---");
        $user = User::findOne($this->user_id);
        if (!$user) {
            return;
        }
        $distribution = Distribution::findOne(['mall_id' => $this->mall_id, 'user_id' => $this->user_id, 'is_delete' => 0]);
        \Yii::warning("DistributionLevelUpgradeJob distribution=".var_export($distribution,true));
        if (!$distribution) {
            $is_apply = DistributionSetting::getValueByKey('is_apply', $this->mall_id);
            if ($is_apply == 0 && $user->is_inviter) {
                $distribution = new Distribution();
                $distribution->mall_id = $this->mall_id;
                $distribution->user_id = $this->user_id;
                $distribution->created_at = time();
                $distribution->level = 0;
                $distribution->is_delete = 0;
                if (!$distribution->save()) {
                    \Yii::warning('成为分销商发生错误' . var_export($distribution,true));
                }
            }
        }
        if (!$distribution) {
            \Yii::warning('用户不是分销商');
            return;
        }
        \Yii::warning("DistributionLevelUpgradeJob execute");
        try{
            if ($distribution) {
                $level_list = DistributionLevel::find()->where(['mall_id' => $this->mall_id, 'is_use' => 1])->andWhere(['>', 'level', $distribution->level])->orderBy('level desc')->all();
                \Yii::warning("DistributionLevelUpgradeJob execute level_list=".var_export($level_list,true));
                if ($level_list) {
                    $flag = false;
                    $level_value = 0;
                    $upgrade_status = 0;
                    /**
                     * @var DistributionLevel $level_list []
                     * @var DistributionLevel $level
                     */
                    foreach ($level_list as $level) {
                        $level_value = $level->level;
                        \Yii::warning("DistributionLevelUpgradeJob level_list level_value={$level_value};upgrade_type_goods=".$level->upgrade_type_goods);
                        if ($level->upgrade_type_goods) {
                            $upgrade_status = Distribution::UPGRADE_STATUS_GOODS;
                            \Yii::warning("DistributionLevelUpgradeJob level_list level->goods_type=".$level->goods_type);
                            if ($level->goods_type == 1) {//任意商品
                                $order = CommonOrder::find()->where(['user_id' => $this->user_id, 'mall_id' => $this->mall_id, 'is_delete' => 0, 'status' => CommonOrder::STATUS_IS_COMPLETE])->exists();
                                if ($order) {
                                    $flag = true;
                                    break;
                                }
                            } elseif ($level->goods_type == 2) {
                                $goods_warehouse_ids = SerializeHelper::decode($level->goods_warehouse_ids);
                                \Yii::warning("DistributionLevelUpgradeJob level_list goods_warehouse_ids=".var_export($goods_warehouse_ids,true));
                                if (!empty($goods_warehouse_ids)) {
                                    $goods_ids = Goods::find()->where(['mall_id' => $this->mall_id, 'is_delete' => 0])->andWhere(['goods_warehouse_id' => $goods_warehouse_ids])->select('id')->column();
                                    \Yii::warning("DistributionLevelUpgradeJob level_list goods_ids=".var_export($goods_ids,true));
                                    if (!empty($goods_ids)) {
                                        $isGoods = CommonOrder::find()
                                            ->alias('co')
                                            ->leftJoin(['cod' => CommonOrderDetail::tableName()], 'cod.common_order_id=co.id')
                                            ->andWhere(['co.user_id' => $this->user_id, 'co.mall_id' => $this->mall_id, 'co.is_delete' => 0, 'co.status' => CommonOrder::STATUS_IS_COMPLETE])
                                            ->andWhere(['cod.goods_id' => $goods_ids])
                                            ->andWhere(['cod.goods_type' => CommonOrderDetail::TYPE_MALL_GOODS])
                                            ->exists();
                                        if ($isGoods) {
                                            $flag = true;
                                            break;
                                        }
                                    }
                                }
                            }
                        }
                        \Yii::warning("DistributionLevelUpgradeJob level_list level->upgrade_type_condition=".$level->upgrade_type_condition);
                        if ($level->upgrade_type_condition) { //使用条件升级
                            $upgrade_status = Distribution::UPGRADE_STATUS_CONDITION;
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
                            \Yii::warning("DistributionLevelUpgradeJob userGrowth ");
                            $growth_list = UserGrowth::find()->where(['user_id' => $this->user_id, 'is_delete' => 0])->all();
                            \Yii::warning("DistributionLevelUpgradeJob userGrowth");
                            //满足任意条件
                            if ($level->condition_type == 1) {
                                foreach ($checked_condition_keys as $key) {
                                    if ($key == 0) {
                                        //第一个条件
                                        //条件一还没有处理
                                        $reach_price = $checked_condition_values[$key]['value']['val'];
                                        $reach_num = $checked_condition_values[$key]['value']['val1'];
                                        $user_child_list = UserChildren::find()->alias('uc')
                                            ->andWhere(['uc.level' => 1, 'uc.user_id' => $this->user_id, 'mall_id' => $this->mall_id])
                                            ->asArray()
                                            ->all();
                                        if (count($user_child_list) >= $reach_num) {
                                            $j = 0;
                                            foreach ($user_child_list as $child) {
                                                $sum_price = CommonOrderDetail::find()->where(['user_id' => $child['child_id'], 'status' => 1])->sum('price');
                                                if ($sum_price && $sum_price >= $reach_price) {
                                                    $j++;
                                                }
                                                if ($j >= $reach_num) {
                                                    $flag = true;
                                                    break 3;
                                                }

                                            }
                                        }
                                    }


                                    if ($key == 1) {
                                        \Yii::warning('条件2');
                                        //第二个条件   分销订单满多少元
                                        $reach = $checked_condition_values[$key]['value']['val'];
                                        $sum_price = PriceLog::find()->where(['user_id' => $this->user_id, 'status' => 1, 'sign' => 'distribution'])->sum('price');
                                        if ($sum_price >= $reach) {
                                            $flag = true;
                                            break 2;
                                        }
                                    }
                                    if ($key == 2) {

                                        //分销订单数量满
                                        $reach = $checked_condition_values[$key]['value']['val'];
                                        $order_count = CommonOrder::find()->alias('co')
                                            ->leftJoin(['cod' => CommonOrderDetail::tableName()], 'cod.common_order_id=co.id')
                                            ->leftJoin(['l' => PriceLog::tableName()], 'l.common_order_detail_id=cod.id')
                                            ->where(['l.user_id' => $this->user_id, 'l.sign' => 'distribution'])
                                            ->count();
                                        if ($order_count >= $reach) {
                                            $flag = true;
                                            break 2;
                                        }
                                    }
                                    if ($key == 3) {
                                        //一级分销订单金额满
                                        $reach = $checked_condition_values[$key]['value']['val'];
                                        $sum_price = UserChildren::find()->alias('uc')
                                            ->leftJoin(['cod' => CommonOrderDetail::tableName()], 'cod.user_id=uc.child_id')
                                            ->leftJoin(['l' => PriceLog::tableName()], 'l.common_order_detail_id=cod.id')
                                            ->andWhere(['uc.level' => 1, 'l.sign' => 'distribution', 'uc.user_id' => $this->user_id])
                                            ->sum('cod.price');
                                        if ($sum_price && $sum_price >= $reach) {
                                            $flag = true;
                                            break 2;
                                        }
                                    }

                                    if ($key == 4) {
                                        //一级分销的订单数量
                                        $reach = $checked_condition_values[$key]['value']['val'];
                                        $sum_price = UserChildren::find()->alias('uc')
                                            ->leftJoin(['cod' => CommonOrderDetail::tableName()], 'cod.user_id=uc.child_id')
                                            ->leftJoin(['co' => CommonOrder::tableName()], 'cod.common_order_id=co.id')
                                            ->leftJoin(['l' => PriceLog::tableName()], 'l.common_order_detail_id=cod.id')
                                            ->andWhere(['uc.level' => 1, 'l.sign' => 'distribution', 'uc.user_id' => $this->user_id])
                                            ->select('co.order_id')
                                            ->count('co.id');
                                        if ($sum_price && $sum_price >= $reach) {
                                            $flag = true;
                                            break 2;
                                        }
                                    }
                                    if ($key == 5) {
                                        //自购订单金额
                                        $reach = $checked_condition_values[$key]['value']['val'];
                                        $sum_price = CommonOrderDetail::find()->alias('cod')
                                            ->andWhere(['cod.user_id' => $this->user_id, 'cod.status' => 1, 'cod.is_delete' => 0])
                                            ->sum('cod.price');
                                        if ($sum_price && $sum_price >= $reach) {
                                            $flag = true;
                                            break 2;
                                        }
                                    }


                                    if ($key == 6) {
                                        //自购订单数量
                                        $reach = $checked_condition_values[$key]['value']['val'];
                                        $sum_price = CommonOrder::find()->alias('co')
                                            ->leftJoin(['cod' => CommonOrderDetail::tableName()], 'co.id=cod.common_order_id')
                                            ->andWhere(['cod.user_id' => $this->user_id, 'cod.status' => 1, 'cod.is_delete' => 0])
                                            ->count();
                                        if ($sum_price && $sum_price >= $reach) {
                                            $flag = true;
                                            break 2;
                                        }
                                    }
                                    if ($key == 7) {
                                        //粉丝人数满
                                        $reach = $checked_condition_values[$key]['value']['val'];
                                        $sum_price = UserChildren::find()->where(['user_id' => $this->user_id, 'is_delete' => 0])
                                            ->count();
                                        if ($sum_price && $sum_price >= $reach) {
                                            $flag = true;
                                            break 2;
                                        }
                                    }

                                    if ($key == 8) {
                                        //一级粉丝人数满
                                        $reach = $checked_condition_values[$key]['value']['val'];
                                        $sum_price = UserChildren::find()->where(['user_id' => $this->user_id, 'is_delete' => 0])
                                            ->andWhere(['level' => 1])
                                            ->count();
                                        if ($sum_price && $sum_price >= $reach) {
                                            $flag = true;
                                            break 2;
                                        }
                                    }
                                    if ($key == 9) {

                                        //粉丝分销人数满
                                        $reach = $checked_condition_values[$key]['value']['val'];
                                        $sum_price = UserChildren::find()
                                            ->alias('uc')
                                            ->leftJoin(['d' => Distribution::tableName()], 'd.user_id=uc.child_id')
                                            ->where(['uc.user_id' => $this->user_id, 'uc.is_delete' => 0, 'd.is_delete' => 0])
                                            ->count();
                                        if ($sum_price && $sum_price >= $reach) {
                                            $flag = true;
                                            break 2;
                                        }
                                    }
                                    if ($key == 10) {
                                        //一级粉丝分销人数满
                                        $reach = $checked_condition_values[$key]['value']['val'];
                                        $sum_price = UserChildren::find()
                                            ->alias('uc')
                                            ->leftJoin(['d' => Distribution::tableName()], 'd.user_id=uc.child_id')
                                            ->where(['uc.user_id' => $this->user_id, 'uc.is_delete' => 0, 'd.is_delete' => 0])
                                            ->andWhere(['uc.level' => 1])
                                            ->count();
                                        if ($sum_price && $sum_price >= $reach) {
                                            $flag = true;
                                            break 2;
                                        }
                                    }

                                    if ($key == 11) {
                                        $reach = $checked_condition_values[$key]['value']['val'];
                                        if ($user->total_income && $user->total_income >= $reach) {
                                            $flag = true;
                                            break 2;
                                        }
                                    }
                                }

                            }

                            //满足全部
                            if ($level->condition_type == 2) {
                                foreach ($checked_condition_keys as $key) {
                                    if ($key == 0) {
                                        //第一个条件
                                        //条件一还没有处理
                                        $reach_price = $checked_condition_values[$key]['value']['val'];
                                        $reach_num = $checked_condition_values[$key]['value']['val1'];
                                        $user_child_list = UserChildren::find()->alias('uc')
                                            ->andWhere(['uc.level' => 1, 'uc.user_id' => $this->user_id, 'mall_id' => $this->mall_id])
                                            ->asArray()
                                            ->all();
                                        if (count($user_child_list) >= $reach_num) {
                                            $j = 0;
                                            foreach ($user_child_list as $child) {
                                                $sum_price = CommonOrderDetail::find()->where(['user_id' => $child['child_id'], 'status' => 1])->sum('price');
                                                if ($sum_price && $sum_price >= $reach_price) {
                                                    $j++;
                                                }
                                                if ($j >= $reach_num) {
                                                    $flag = true;
                                                    break;
                                                }
                                            }
                                        }
                                        if (!$flag) {
                                            $flag = false;
                                            break;
                                        }
                                    }

                                    if ($key == 1) {
                                        //第二个条件   分销订单满多少元
                                        $reach = $checked_condition_values[$key]['value']['val'];
                                        $sum_price = PriceLog::find()->where(['user_id' => $this->user_id, 'status' => 1, 'sign' => 'distribution'])->sum('price');
                                        if ($sum_price >= $reach) {
                                            $flag = true;
                                        } else {
                                            $flag = false;
                                            break;
                                        }
                                    }

                                    if ($key == 2) {
                                        //分销订单数量满
                                        $reach = $checked_condition_values[$key]['value']['val'];
                                        $order_count = CommonOrder::find()->alias('co')
                                            ->leftJoin(['cod' => CommonOrderDetail::tableName()], 'cod.common_order_id=co.id')
                                            ->leftJoin(['l' => PriceLog::tableName()], 'l.common_order_detail_id=cod.id')
                                            ->where(['l.user_id' => $this->user_id, 'l.sign' => 'distribution'])
                                            ->count();
                                        if ($order_count >= $reach) {
                                            $flag = true;
                                        } else {
                                            $flag = false;
                                            break;
                                        }
                                    }

                                    if ($key == 3) {
                                        //一级分销订单金额满
                                        $reach = $checked_condition_values[$key]['value']['val'];
                                        $sum_price = UserChildren::find()->alias('uc')
                                            ->leftJoin(['cod' => CommonOrderDetail::tableName()], 'cod.user_id=uc.child_id')
                                            ->leftJoin(['l' => PriceLog::tableName()], 'l.common_order_detail_id=cod.id')
                                            ->andWhere(['uc.level' => 1, 'l.sign' => 'distribution', 'uc.user_id' => $this->user_id])
                                            ->sum('cod.price');
                                        if ($sum_price && $sum_price >= $reach) {
                                            $flag = true;
                                        } else {
                                            $flag = false;
                                            break;
                                        }
                                    }

                                    if ($key == 4) {
                                        //一级分销的订单数量
                                        $reach = $checked_condition_values[$key]['value']['val'];
                                        $sum_price = UserChildren::find()->alias('uc')
                                            ->leftJoin(['cod' => CommonOrderDetail::tableName()], 'cod.user_id=uc.child_id')
                                            ->leftJoin(['co' => CommonOrder::tableName()], 'cod.common_order_id=co.id')
                                            ->leftJoin(['l' => PriceLog::tableName()], 'l.common_order_detail_id=cod.id')
                                            ->andWhere(['uc.level' => 1, 'l.sign' => 'distribution', 'uc.user_id' => $this->user_id])
                                            ->select('co.order_id')
                                            ->count('co.id');
                                        if ($sum_price && $sum_price >= $reach) {
                                            $flag = true;
                                        } else {
                                            $flag = false;
                                            break;
                                        }
                                    }
                                    if ($key == 5) {
                                        //自购订单金额
                                        $reach = $checked_condition_values[$key]['value']['val'];
                                        $sum_price = CommonOrderDetail::find()->alias('cod')
                                            ->andWhere(['cod.user_id' => $this->user_id, 'cod.status' => 1, 'cod.is_delete' => 0])
                                            ->sum('cod.price');
                                        if ($sum_price && $sum_price >= $reach) {
                                            $flag = true;

                                        } else {
                                            $flag = false;
                                            break;
                                        }
                                    }


                                    if ($key == 6) {
                                        //自购订单数量
                                        $reach = $checked_condition_values[$key]['value']['val'];
                                        $sum_price = CommonOrder::find()->alias('co')
                                            ->leftJoin(['cod' => CommonOrderDetail::tableName()], 'co.id=cod.common_order_id')
                                            ->andWhere(['cod.user_id' => $this->user_id, 'cod.status' => 1, 'cod.is_delete' => 0])
                                            ->count();
                                        if ($sum_price && $sum_price >= $reach) {
                                            $flag = true;
                                        } else {
                                            $flag = false;
                                            break;
                                        }
                                    }
                                    if ($key == 7) {
                                        //粉丝人数满
                                        $reach = $checked_condition_values[$key]['value']['val'];
                                        $sum_price = UserChildren::find()->where(['user_id' => $this->user_id, 'is_delete' => 0])
                                            ->count();
                                        if ($sum_price && $sum_price >= $reach) {
                                            $flag = true;

                                        } else {
                                            $flag = false;
                                            break;
                                        }
                                    }

                                    if ($key == 8) {
                                        //一级粉丝人数满
                                        $reach = $checked_condition_values[$key]['value']['val'];
                                        $sum_price = UserChildren::find()->where(['user_id' => $this->user_id, 'is_delete' => 0])
                                            ->andWhere(['level' => 1])
                                            ->count();
                                        if ($sum_price && $sum_price >= $reach) {
                                            $flag = true;

                                        } else {
                                            $flag = false;
                                            break;
                                        }
                                    }
                                    if ($key == 9) {
                                        //粉丝分销人数满
                                        $reach = $checked_condition_values[$key]['value']['val'];
                                        $sum_price = UserChildren::find()
                                            ->alias('uc')
                                            ->leftJoin(['d' => Distribution::tableName()], 'd.user_id=uc.child_id')
                                            ->where(['uc.user_id' => $this->user_id, 'uc.is_delete' => 0, 'd.is_delete' => 0])
                                            ->count();
                                        if ($sum_price && $sum_price >= $reach) {
                                            $flag = true;
                                        } else {
                                            $flag = false;
                                            break;
                                        }
                                    }
                                    if ($key == 10) {
                                        //一级粉丝分销人数满
                                        $reach = $checked_condition_values[$key]['value']['val'];
                                        $sum_price = UserChildren::find()
                                            ->alias('uc')
                                            ->leftJoin(['d' => Distribution::tableName()], 'd.user_id=uc.child_id')
                                            ->where(['uc.user_id' => $this->user_id, 'uc.is_delete' => 0, 'd.is_delete' => 0])
                                            ->andWhere(['uc.level' => 1])
                                            ->count();
                                        if ($sum_price && $sum_price >= $reach) {
                                            $flag = true;
                                        } else {
                                            $flag = false;
                                            break;
                                        }
                                    }

                                    if ($key == 11) {
                                        $reach = $checked_condition_values[$key]['value']['val'];
                                        if ($user->total_income && $user->total_income >= $reach) {
                                            $flag = true;
                                        } else {
                                            $flag = false;
                                            break;
                                        }
                                    }
                                }
                                if ($flag) {
                                    break 1;
                                }
                            }
                        }
                        \Yii::warning("DistributionLevelUpgradeJob level_list foreach");
                    }
                    \Yii::warning("DistributionLevelUpgradeJob flag=".$flag.";level_value={$level_value}");
                    if ($flag) {
                        \Yii::warning("用户：{$this->user_id}，分销商升级成功");
                        $distribution->level = $level_value;
                        $distribution->upgrade_status = $upgrade_status;
                        $distribution->upgrade_level_at = time();
                        $distribution->save();
                    } else {
                        \Yii::warning("用户：{$this->user_id}，分销商升级失败");
                    }
                }
            }
        }catch(\Exception $ex){
            \Yii::error("DistributionLevelUpgradeJob execute excetion errorMsg=".CommonLogic::getExceptionMessage($ex));
        }

    }
}