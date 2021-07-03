<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 经销佣金订单处理任务类
 * Author: zal
 * Date: 2020-05-25
 * Time: 17:05
 */

namespace app\plugins\agent\jobs;

use app\helpers\SerializeHelper;
use app\logic\CommonLogic;
use app\models\CommonOrderDetail;
use app\models\Mall;
use app\models\PriceLog;
use app\models\User;
use app\models\UserChildren;
use app\models\UserParent;
use app\plugins\agent\models\Agent;
use app\plugins\agent\models\AgentGoods;
use app\plugins\agent\models\AgentGoodsDetail;
use app\plugins\agent\models\AgentLevel;
use app\plugins\agent\models\AgentLevelNum;
use app\plugins\agent\models\AgentPriceLogType;
use app\plugins\agent\models\AgentSetting;
use app\plugins\agent\Plugin;
use yii\base\Component;
use yii\base\Exception;
use yii\queue\JobInterface;
use yii\queue\Queue;

class AgentLogJob extends Component implements JobInterface
{
    /** @var CommonOrderDetail $order */
    public $order;
    public $common_order_detail_id;
    /** @var int 处理类型 1新增订单    2状态变更   3、一支付就结算 */
    public $type;

    /**
     *
     * @param Queue $queue
     * @return mixed|void
     * @throws \Exception
     */

    //TODO 还需要加入其他筛选添加 例如是商城商品还是其他商品
    public function execute($queue)
    {

        \Yii::warning('经销记录队列开始执行');
        $order = CommonOrderDetail::findOne($this->common_order_detail_id);
        if (!$order) {
            \Yii::warning("---公共订单不存在：{$this->common_order_detail_id}  商城ID{$this->order->mall_id}---");
            return;
        }
        $this->order = $order;
        $mall = Mall::findOne($this->order->mall_id);
        $plugin = new Plugin();
        $sign = 'mall';
        $sign = $plugin->getName();
        if (!$mall) {
            \Yii::warning("---处理经销队列时候商城不存在公共订单ID：{$this->common_order_detail_id} 商城ID{$this->order->mall_id}---");
            return;
        }
        \Yii::$app->setMall($mall);
        $is_enable = AgentSetting::getValueByKey(AgentSetting::IS_ENABLE);
        if (!$is_enable) {
            \Yii::warning('经销没有启用');
            return;
        }
        \Yii::warning("---经销佣金订单记录处理开始---");
        //这里需要从common_order_detail 里面获取商品的类型

        \Yii::warning('当前的type' . $this->type);
        $user = User::findOne($order->user_id);
        if (!$user) {
            \Yii::warning('经销订单找不到用户');
            return;
        }
        $buyer_is_agent = 0;

        //1创建订单
        if ($this->type == 1) { //创建订单
            \Yii::warning('创建订单');
            //默认的分佣设置
            $is_contain_self = AgentSetting::getValueByKey(AgentSetting::IS_CONTAIN_SELF, $mall->id);//团队是否包含自己
            $is_equal = AgentSetting::getValueByKey(AgentSetting::IS_EQUAL);//是否启用平级奖
            $is_self_buy = AgentSetting::getValueByKey(AgentSetting::IS_SELF_BUY);//经销商内购
            $level = intval(AgentSetting::getValueByKey(AgentSetting::AGENT_LEVEL));//奖励层级
            $equal_level = intval(AgentSetting::getValueByKey(AgentSetting::EQUAL_LEVEL));//平级层级
            $is_equal_self = intval(AgentSetting::getValueByKey(AgentSetting::IS_EQUAL_SELF));//平级层级
            $over_level = intval(AgentSetting::getValueByKey(AgentSetting::OVER_LEVEL));//平级层级

            //现在是默认商城商品订单
            $is_alone = 0;
            $agent_detail_list = null;
            $goods_type = $this->order->goods_type;

            $agent_goods = null;
            if ($goods_type == CommonOrderDetail::TYPE_MALL_GOODS) {
                //商城商品
                $agent_goods = AgentGoods::findOne(['goods_id' => $this->order->goods_id, 'is_delete' => 0, 'is_alone' => 1]);  //这里要加入
                if ($agent_goods) {
                    //独立设置
                    $is_alone = 1;
                    $agent_detail_list = AgentGoodsDetail::find()->andWhere(['agent_goods_id' => $agent_goods->id, 'is_delete' => 0])->all();
                }
            }

            //首先找出所有的经销商；
            $parent_agent_ids = UserParent::find()
                ->alias('up')
                ->leftJoin(['a' => Agent::tableName()], 'a.user_id=up.parent_id')
                ->andWhere(['up.user_id' => $order->user_id, 'up.is_delete' => 0, 'a.is_delete' => 0])
                ->orderBy('up.level ASC')
                ->select('a.id')->column();
            $parent_agent_list = [];
            if (count($parent_agent_ids)) {
                foreach ($parent_agent_ids as $id) {
                    $agent = Agent::findOne($id);
                    if ($agent) {
                        $parent_agent_list[] = $agent;
                    }
                }
            }
            $agent = Agent::findOne(['user_id' => $this->order->user_id, 'is_delete' => 0]);
            if ($agent) {
                $buyer_is_agent = 1; //购买者是经销商
            }
            \Yii::warning('--------------------------------------------------------------');
            if ($is_self_buy) {
                \Yii::warning('开启自购');
                \Yii::warning('--------------------------------------------------------------');
                if ($agent) {
                    array_unshift($parent_agent_list, $agent);
                    $agent_list = $parent_agent_list;
                } else {
                    $agent_list = $parent_agent_list;
                }
            } else {
                $agent_list = $parent_agent_list;
            }
            \Yii::warning("agentLogJob execute agent_list = ".var_export($agent_list,true));
            $can_price_agent = [];
            $over_price_agent = [];
            $current_agent_level = 0;
            foreach ($agent_list as $i => $agent) {
                if ($agent->level > $current_agent_level) {
                    $can_price_agent[] = $agent;
                    $current_agent_level = $agent->level;
                } else {
                    if ($i > 0) { //被越级，这里要排除第一个经销商
                        $over_price_agent[] = $agent;
                    }
                }
            }
            \Yii::warning("agentLogJob execute can_price_agent = ".var_export($can_price_agent,true));
            \Yii::warning("agentLogJob execute over_price_agent = ".var_export($over_price_agent,true));
            for ($i = 0; $i < $level; $i++) {
                //层级
                if (count($can_price_agent) > $i) {
                    /**
                     * @var Agent $agent
                     */
                    $agent = $can_price_agent[$i];
                    if ($agent) {
                        \Yii::warning('在经销商里面');
                        $user_child_level = 0;
                        $user_child = UserChildren::findOne(['user_id' => $agent->user_id, 'child_id' => $order->user_id]);
                        if ($user_child) {
                            $user_child_level = $user_child->level;
                        }
                        //已经分钱的总额
                        $log_total_price = PriceLog::find()->where(['common_order_detail_id' => $this->common_order_detail_id, 'is_delete' => 0, 'sign' => $sign])->sum('price');
                        \Yii::warning("agentLogJob execute level= ".$agent->level.";user_id= ".$agent->user_id.";log_total_price = ".$log_total_price);
                        $log_total_price = $log_total_price ?? 0;
                        //这里默认走级差
                        $log = PriceLog::findOne(['common_order_detail_id' => $this->common_order_detail_id, 'is_delete' => 0, 'user_id' => $agent->user_id, 'sign' => $sign]);
                        $price = 0;

                        if (!$log) {
                            $log = new PriceLog();
                            $log->mall_id = \Yii::$app->mall->id;
                            $log->user_id = $agent->user_id;
                            $log->status = 0;
                            $log->common_order_detail_id = $this->common_order_detail_id;
                            $log->child_id = $this->order->user_id;
                            $log->level = $user_child_level;
                            $log->order_id = $this->order->order_id;
                            $log->sign = $sign;
                        }

                        if ($is_alone) {//单独设置的

                            \Yii::warning('独立设置经销=======================================');

                            foreach ($agent_detail_list as $detail) {
                                /**
                                 * @var AgentGoodsDetail $detail ;
                                 */
                                if ($detail->level == $agent->level) {
                                    if ($agent_goods->agent_price_type) {//固定金额
                                        $price = $detail->agent_price * $order->num;
                                    } else {
                                        $price = $detail->agent_price * $order->price / 100;
                                    }
                                    break;
                                }
                            }
                        } else {
                            $agent_level = AgentLevel::findOne(['level' => $agent->level, 'mall_id' => \Yii::$app->mall->id, 'is_delete' => 0]);
                            if ($agent_level) { //找到经销商等级
                                if ($agent_level->agent_price_type) {
                                    $price = $agent_level->agent_price * $order->num;
                                } else {
                                    $price = $agent_level->agent_price * $order->price / 100;
                                }
                            }
                        }
                        \Yii::warning("agentLogJob execute user_id= ".$agent->user_id.";price = ".$price);
                        if ($price) {
                            $price -= $log_total_price;
                        }
                        \Yii::warning("agentLogJob execute user_id= ".$agent->user_id.";price = ".$price);
                        if ($price && $price > 0) {
                            $log->price = $price;
                            if (!$log->save()) {
                                \Yii::warning(json_encode($log->getErrors()));
                            } else {
                                ###################################################
                                # 扣减经销商相关等级名额
                                # TIP:没有名额或者名额不足，则不发相关奖励
                                ###################################################
                                $res = AgentLevelNum::handleNum($agent,$agent->level,1,'decrease');
                                echo '开始扣减名额,结果:'.$res.PHP_EOL;
                                if($res){
                                    //扣除成功发放团队将
                                    $log_type = new AgentPriceLogType();
                                    $log_type->mall_id = $order->mall_id;
                                    $log_type->type = AgentPriceLogType::TYPE_AGENT;
                                    $log_type->price_log_id = $log->id;
                                    $log_type->save();
                                    $user = User::findOne($log->user_id);
                                    \Yii::$app->currency->setUser($user)->income
                                        ->add(floatval($log->price), "经销商提成记录ID：{$log->id} 的冻结佣金", 0);
                                    $agent->frozen_price += $price;
                                    $order_count = PriceLog::find()->where(['user_id' => $log->user_id, 'is_delete' => 0, 'sign' => $sign])->groupBy('order_id')->count();
                                    $agent->total_order = $order_count;
                                    $agent->save();
                                    \Yii::warning('团队奖保存成功');
                                }

                                //计算越级奖
                                \Yii::warning('********************************************开始计算越级奖***********************************');
                                if ($over_level > 0) {
                                    if (count($over_price_agent)) {
                                        $current_agent_list = [];
                                        foreach ($agent_list as $a => $agent1) {
                                            if ($agent1->user_id == $log->user_id) {
                                                $current_agent_list = array_slice($agent_list, $a + 1, count($agent_list) - $a - 1);
                                                break;
                                            }
                                        }
                                        \Yii::warning("agentLogJob execute current_agent_list = ".var_export($current_agent_list,true));
                                        $current_level = $agent->level;
                                        $times = 0;
                                        foreach ($current_agent_list as $cagent) {
                                            if ($times >= $over_level) {
                                                break;
                                            }
                                            if ($cagent->level < $current_level) {
                                                $agent_level = AgentLevel::findOne(['level' => $cagent->level, 'mall_id' => $cagent->mall_id]);
                                                if ($agent_level && $agent_level->over_agent_price > 0) {
                                                    $user_child_level = 0;
                                                    $user_child = UserChildren::findOne(['user_id' => $cagent->user_id, 'child_id' => $order->user_id]);
                                                    if ($user_child) {
                                                        $user_child_level = $user_child->level;
                                                    }
                                                    $price = 0;
                                                    if ($is_alone) {
                                                        \Yii::warning('独立设置越级=======================================');

                                                        foreach ($agent_detail_list as $detail) {
                                                            /**
                                                             * @var AgentGoodsDetail $detail ;
                                                             */
                                                            if ($detail->level == $cagent->level) {
                                                                $price = $detail->over_agent_price * $log->price / 100;
                                                                break;
                                                            }
                                                        }
                                                    } else {
                                                        $price = $agent_level->over_agent_price * $log->price / 100;
                                                    }
                                                    $over_log = new PriceLog();
                                                    $over_log->mall_id = \Yii::$app->mall->id;
                                                    $over_log->user_id = $cagent->user_id;
                                                    $over_log->status = 0;
                                                    $over_log->common_order_detail_id = $this->common_order_detail_id;
                                                    $over_log->child_id = $this->order->user_id;
                                                    $over_log->level = $user_child_level;
                                                    $over_log->order_id = $this->order->order_id;
                                                    $over_log->sign = $sign;
                                                    $over_log->price = $price;
                                                    if ($price && $price > 0) {
                                                        if ($over_log->save()) {
                                                            $log_type = new AgentPriceLogType();
                                                            $log_type->mall_id = $order->mall_id;
                                                            $log_type->type = AgentPriceLogType::TYPE_OVER;
                                                            $log_type->price_log_id = $over_log->id;
                                                            $log_type->save();
                                                            $user = User::findOne($over_log->user_id);
                                                            \Yii::$app->currency->setUser($user)->income
                                                                ->add(floatval($log->price), "经销商越级奖记录ID：{$over_log->id} 的冻结佣金", 0);
                                                            $cagent->frozen_price += $price;
                                                            $order_count = PriceLog::find()->where(['user_id' => $over_log->user_id, 'is_delete' => 0, 'sign' => $sign])->groupBy('order_id')->count();
                                                            $cagent->total_order = $order_count;
                                                            $cagent->save();
                                                            \Yii::warning('越级奖保存成功');
                                                        }
                                                    }
                                                    $times++;
                                                } else {
                                                    break;
                                                }
                                            } else {
                                                break;
                                            }
                                        }
                                    }
                                }
                                \Yii::warning('********************************************计算越级奖结束***********************************');
                            }
                        } else {
                            \Yii::warning('经销佣金是0无需发放佣金');
                        }
                    }
                }
            }

            \Yii::warning('******************************团队奖分完了********************************');

            \Yii::warning('******************************开始执行平级奖********************************');
            if (!$is_equal) {
                \Yii::warning('系统未开启平级奖');
                return;
            }
            if ($buyer_is_agent) {
                if ($is_self_buy) {
                    if (!$is_equal_self) {
                        array_shift($agent_list);
                    }
                }
            }
            try{
                $agent_level_list = AgentLevel::find()->where(['mall_id' => $order->mall_id, 'is_delete' => 0, 'is_use' => 1])->orderBy('level ASC')->asArray()->all();
                foreach ($agent_level_list as &$level) {
                    foreach ($agent_list as $agent) {
                        if ($agent->level == $level['level']) {
                            $count = isset($level['agent_list']) ? count($level['agent_list']) : 0;
                            if ($count <= $equal_level) {
                                $level['agent_list'][] = $agent;
                            } else {
                                break 1;
                            }
                        }
                    }
                    \Yii::warning("agentLogJob execute level[agent_list] = ".var_export($level['agent_list'],true));
                    //平级的没什么问题了
                    if (isset($level['agent_list'])) {
                        for ($i = 1; $i < count($level['agent_list']); $i++) {
                            /**
                             * @var Agent $agent
                             */
                            $agent = $level['agent_list'][$i];
                            if ($agent) {
                                $log = PriceLog::findOne(['common_order_detail_id' => $this->common_order_detail_id, 'is_delete' => 0, 'user_id' => $agent->user_id, 'sign' => 'agent']);

                                $user_child_level = 0;
                                $user_child = UserChildren::findOne(['user_id' => $agent->user_id, 'child_id' => $order->user_id]);
                                if ($user_child) {
                                    $user_child_level = $user_child->level;
                                }
                                if (!$log) {
                                    $log = new PriceLog();
                                    $log->mall_id = \Yii::$app->mall->id;
                                    $log->user_id = $agent->user_id;
                                    $log->status = 0;
                                    $log->common_order_detail_id = $this->common_order_detail_id;
                                    $log->child_id = $order->user_id;
                                    $log->level = $user_child_level;
                                    $log->order_id = $order->order_id;
                                    $log->sign = 'agent';
                                } else {
                                    $price_type = AgentPriceLogType::findOne(['price_log_id' => $log->id, 'type' => AgentPriceLogType::TYPE_EQUAL]);
                                    if (!$price_type) {
                                        $log = new PriceLog();
                                        $log->mall_id = \Yii::$app->mall->id;
                                        $log->user_id = $agent->user_id;
                                        $log->status = 0;
                                        $log->common_order_detail_id = $this->common_order_detail_id;
                                        $log->child_id = $order->user_id;
                                        $log->level = $user_child_level;
                                        $log->order_id = $order->order_id;
                                        $log->sign = 'agent';
                                    } else {
                                        $log = null;
                                    }
                                }
                                if ($log) {
                                    $price = 0;
                                    if ($is_alone) {
                                        \Yii::warning('独立设置平级=======================================');

                                        foreach ($agent_detail_list as $detail) {
                                            /**
                                             * @var AgentGoodsDetail $detail ;
                                             */
                                            if ($agent_goods->equal_price_type) {//固定金额
                                                $price = $detail->equal_price * $order->num;
                                            } else {
                                                $price = $detail->equal_price * $order->price / 100;
                                            }
                                        }
                                    } else {
                                        $equal_price_type = $level['equal_price_type'];
                                        $equal_price = $level['equal_price'];
                                        if ($equal_price_type) {
                                            $price = $equal_price * $order->num;
                                        } else {
                                            $price = $equal_price * $order->price / 100;
                                        }
                                    }
                                    if ($price && $price > 0) {
                                        $log->price = $price;
                                        if (!$log->save()) {
                                            \Yii::warning('平级奖记录保存错误.' . var_export($log->getErrors(), true));
                                        } else {
                                            $price_type = new AgentPriceLogType();
                                            $price_type->price_log_id = $log->id;
                                            $price_type->mall_id = $order->mall_id;
                                            $price_type->type = AgentPriceLogType::TYPE_EQUAL;
                                            $price_type->save();
                                            $user = User::findOne($log->user_id);
                                            \Yii::$app->currency->setUser($user)->income
                                                ->add(floatval($log->price), "经销商提成记录ID：{$log->id} 的平级奖佣金", 0);
                                            $agent->frozen_price += $equal_price;
                                            $order_count = PriceLog::find()->where(['user_id' => $log->user_id, 'is_delete' => 0, 'sign' => $sign])->groupBy('order_id')->count();
                                            $agent->total_order = $order_count;
                                            $agent->save();
                                            \Yii::warning('保存成功');
                                        }
                                    }
                                } else {
                                    \Yii::warning('log是空的,不需要保存！');
                                }
                            }
                        }
                    }
                }
            }catch (\Exception $ex){
                \Yii::error("agentLogJob execute 执行平级奖出现 equal_error ".CommonLogic::getExceptionMessage($ex));
            }
        }
        //这里是订单状态改变
        if ($this->type == 2) {
            PriceLog::updateAll(['status' => $this->order->status], ['common_order_detail_id' => $this->common_order_detail_id, 'status' => 0, 'sign' => $sign]);
            //有效   更改当前的经销记录状态
            $log_list = PriceLog::find()->andWhere(['common_order_detail_id' => $this->common_order_detail_id, 'is_delete' => 0, 'sign' => $sign, 'is_price' => 0])->all();
            foreach ($log_list as $log) {
                if ($this->order->status == 1) {
                    \Yii::warning('订单符合分润');
                    /**
                     * @var PriceLog $log
                     */
                    $log->status = 1;
                    //开始佣金到账
                    $user = User::findOne($log->user_id);
                    \Yii::$app->currency->setUser($user)->income
                        ->add(floatval($log->price), "经销商提成记录ID：{$log->id} 的佣金发放", 0, 1);
                    $log->is_price = 1;
                    if (!$log->save()) {
                        \Yii::warning('佣金记录发放保存失败：' . SerializeHelper::encode($log->getErrors()));
                    } else {
                        \Yii::warning('日志保存成功Le');
                        $agent = Agent::findOne(['user_id' => $log->user_id, 'is_delete' => 0]);
                        if ($agent) {
                            try {
                                \Yii::warning('找到经销商');
                                $agent->total_price += floatval($log->price);
                                $agent->frozen_price -= floatval($log->price);
                                if (!$agent->save()) {
                                    \Yii::warning(SerializeHelper::encode($agent));
                                }
                            } catch (Exception $e) {
                                \Yii::warning($e->getMessage());
                            }
                        } else {
                            \Yii::warning('找不到经销商');
                        }
                    }
                }
                if ($this->order->status == -1) {
                    /**
                     * @var PriceLog $log
                     */

                    //保存成功之后要减掉冻结的钱
                    $user = User::findOne($log->user_id);
                    \Yii::$app->currency->setUser($user)->income
                        ->refund(floatval($log->price), "分佣记录ID：{$log->id} 的冻结佣金扣除", 0, 0);
                    $agent = Agent::findOne(['user_id' => $log->user_id, 'is_delete' => 0]);
                    if ($agent) {
                        $agent->frozen_price -= floatval($log->price);
                        if (!$agent->save()) {
                            \Yii::warning(SerializeHelper::encode($agent));
                        }

                        ###################################################
                        # 用户退款返还经销商相关等级名额
                        ###################################################
                        $agent_price_log = AgentPriceLogType::findOne(array('price_log_id'=>$log->id));
                        if(!empty($agent_price_log) && $agent_price_log->type == AgentPriceLogType::TYPE_AGENT){
                            $res = AgentLevelNum::handleNum($agent,$agent->level,1);
                            echo '经销商'.$agent->id.',结果:'.$res.PHP_EOL;
                        }
                    }
                }
            }
        }
        if ($this->type == 3) {
            //有效   更改当前的经销记录状态
            $log_list = PriceLog::find()->andWhere(['common_order_detail_id' => $this->common_order_detail_id, 'is_delete' => 0, 'sign' => $sign, 'is_price' => 0])->all();
            foreach ($log_list as $log) {
                /**
                 * @var PriceLog $log
                 */
                //开始佣金到账
                $user = User::findOne($log->user_id);
                \Yii::$app->currency->setUser($user)->income
                    ->add(floatval($log->price), "经销商提成记录ID：{$log->id} 的佣金发放", 0, 1);
                $log->is_price = 1;
                if (!$log->save()) {
                    \Yii::warning('佣金记录发放保存失败：' . SerializeHelper::encode($log->getErrors()));
                } else {
                    \Yii::warning('日志保存成功Le');
                    $agent = Agent::findOne(['user_id' => $log->user_id, 'is_delete' => 0]);
                    if ($agent) {
                        try {
                            \Yii::warning('找到经销商');
                            $agent->total_price += floatval($log->price);
                            $agent->frozen_price -= floatval($log->price);
                            if (!$agent->save()) {
                                \Yii::warning(SerializeHelper::encode($agent));
                            }
                        } catch (Exception $e) {
                            \Yii::warning($e->getMessage());
                        }
                    } else {
                        \Yii::warning('找不到经销商');
                    }
                }
            }
        }
    }
}