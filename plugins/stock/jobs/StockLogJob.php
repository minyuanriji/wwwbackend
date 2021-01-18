<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 经销佣金订单处理任务类
 * Author: zal
 * Date: 2020-05-25
 * Time: 17:05
 */

namespace app\plugins\stock\jobs;

use app\logic\CommonLogic;
use app\models\CommonOrderDetail;
use app\models\Goods;
use app\models\Mall;
use app\models\User;
use app\models\UserParent;
use app\plugins\stock\forms\common\Common;
use app\plugins\stock\models\StockAgent;
use app\plugins\stock\models\StockAgentGoods;
use app\plugins\stock\models\StockFillJob;
use app\plugins\stock\models\StockGoods;
use app\plugins\stock\models\StockPriceLog;
use app\plugins\stock\models\StockSetting;
use app\plugins\stock\models\UpgradeBag;
use app\plugins\stock\models\UpgradeBagLog;
use app\plugins\stock\Plugin;
use yii\base\Component;
use yii\queue\JobInterface;
use yii\queue\Queue;

class StockLogJob extends Component implements JobInterface
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
        \Yii::warning('StockLogJob execute 库存记录队列开始执行=====================================================================');
        $order = CommonOrderDetail::findOne($this->common_order_detail_id);
        \Yii::warning('StockLogJob execute order='.var_export($order,true));
        if (!$order) {
            \Yii::warning("--- StockLogJob execute 公共订单不存在：{$this->common_order_detail_id}  商城ID{$this->order->mall_id}---");
            return;
        }
        $this->order = $order;
        $mall = Mall::findOne($this->order->mall_id);
        if (!$mall) {
            \Yii::warning("---StockLogJob execute 处理云库存列时候商城不存在公共订单ID：{$this->common_order_detail_id} 商城ID{$this->order->mall_id}---");
            return;
        }
        \Yii::$app->setMall($mall);
        $is_enable = StockSetting::getValueByKey(StockSetting::IS_ENABLE, $mall->id);
        if (!$is_enable) {
            \Yii::warning('StockLogJob execute 云库存没有启用');
            return;
        }
        $is_allow_temp_fill = StockSetting::getValueByKey(StockSetting::IS_ALLOW_TEMP_FILL, $mall->id);
        $temp_fill_time = StockSetting::getValueByKey(StockSetting::TEMP_FILL_TIME, $mall->id);
        $compute_time = StockSetting::getValueByKey(StockSetting::COMPUTE_TIME, $mall->id);

        \Yii::warning("StockLogJob execute is_allow_temp_fill={$is_allow_temp_fill} temp_fill_time=".$temp_fill_time.";compute_time=".$compute_time);

        \Yii::warning("-- StockLogJob execute 货款记录处理开始---");
        //这里需要从common_order_detail 里面获取商品的类型

        \Yii::warning('StockLogJob execute 当前的type' . $this->type);
        $user = User::findOne($order->user_id);
        if (!$user) {
            \Yii::warning('StockLogJob execute 经销订单找不到用户');
            return;
        }

        //1创建订单
        if ($this->type == 1) { //创建订单
            \Yii::warning('StockLogJob execute 创建订单');
            //默认的分佣设置

            //现在是默认商城商品订单
            $is_alone = 0;
            $agent_detail_list = null;
            $goods_type = $this->order->goods_type;
            $agent_goods = null;
            if ($goods_type == CommonOrderDetail::TYPE_MALL_GOODS) {
                try {
                    //商城商品
                    $agent_goods = StockGoods::findOne(['goods_id' => $this->order->goods_id, 'is_delete' => 0]);  //这里要加入
                    if (!$agent_goods) {
                        //独立设置
                        \Yii::warning('不是库存商品');
                        return;
                    }
                    $user_parent_list = UserParent::find()->alias('up')
                        ->leftJoin(['sa' => StockAgent::tableName()], 'sa.user_id=up.parent_id')
                        ->where(['up.user_id' => $order->user_id, 'up.is_delete' => 0, 'sa.is_delete' => 0])
                        ->select('sa.user_id,sa.id')
                        ->orderBy('up.level ASC')
                        ->asArray()
                        ->all();
                    //如果没有上级，直接扣除平台库存
                    if(empty($user_parent_list)){
                        Common::updateGoodsStock($order->goods_id,$order->order_detail_id,$order->num);
                    }
                    \Yii::warning("StockLogJob execute user_parent_list=".var_export($user_parent_list,true));
                } catch (\Exception $e) {
                    \Yii::error("StockLogJob execute error=".CommonLogic::getExceptionMessage($e));
                }
                $total_num = $order->num;
                $remain_num = $total_num;
                $unit_price = $order->price / intval($order->num);
                \Yii::warning("StockLogJob execute unit_price=".$unit_price);
                foreach ($user_parent_list as $item) {
                    if ($total_num <= 0) {
                        break;
                    }
                    $agent_goods = StockAgentGoods::findOne(['goods_id' => $order->goods_id, 'user_id' => $item['user_id']]);
                    \Yii::warning('------------------------------------------------------------------------------------');
                    \Yii::warning('扣库存得用户ID：'.$agent_goods->user_id);
                    if(!empty($agent_goods)){
                        Common::updateGoodsStock($order->goods_id,$order->order_detail_id,$total_num);
                        if ($agent_goods->num >= $total_num) {
                            $log = new StockPriceLog();
                            $log->num = $total_num;
                            $log->price = $order->price;
                            $log->user_id = $item['user_id'];
                            $log->mall_id = $order->mall_id;
                            $log->goods_id = $order->goods_id;
                            $log->common_order_detail_id = $this->common_order_detail_id;
                            if (!$log->save()) {
                                \Yii::error("StockLogJob execute stockFillJob save error=".json_encode($log->getErrors()));
                            }
                            \Yii::warning('StockLogJob execute 扣库存之前这里，还剩：'.$agent_goods->num);
                            $agent_goods->num -= $total_num;
                            if($agent_goods->save() === false){
                                \Yii::error("StockLogJob execute agent_goods update error=".json_encode($agent_goods->getErrors()));
                            }
                            \Yii::warning('------------------------------------------------------------------------------------');
                            \Yii::warning('StockLogJob execute 扣库存这里，还剩：'.$agent_goods->num);
                            break;
                        }
                        if ($agent_goods->num < $total_num) {
                            if ($agent_goods->num > 0) {
                                $log = new StockPriceLog();
                                $log->num = intval($agent_goods->num);
                                $log->price = intval($agent_goods->num) * floatval($unit_price);
                                $log->user_id = $item['user_id'];
                                $log->goods_id = $order->goods_id;
                                $log->mall_id = $order->mall_id;
                                $log->common_order_detail_id = $this->common_order_detail_id;
                                $log->save();
                                $remain_num -= intval($agent_goods->num);
                                $agent_goods->num = 0;
                                $result = $agent_goods->save();
                                \Yii::warning('StockLogJob execute agent_goods->save result：'.$result);
                            }
                            \Yii::warning('StockLogJob execute 用户'.$agent_goods->user_id.'库存不够扣 还需扣库存 total_num：'.$total_num);
                            //库存不足的时候
                            if ($is_allow_temp_fill) { //允许补货
                                \Yii::warning('StockLogJob execute 创建补货队列1 this->common_order_detail_id='.$this->common_order_detail_id);
                                $id = \Yii::$app->queue->delay($temp_fill_time * 60 * 60)->push(new FillStockJob([
                                    'user_id' => $order->user_id,
                                    'common_order_detail_id' => $this->common_order_detail_id,
                                    'mall_id' => $mall->id,
                                    'unit_price' => $unit_price,
                                    'remain_num' => $remain_num,
                                    'total_num' => $total_num,
                                    'goods_id' => $order->goods_id,
                                    'stock_user_id' => $item['user_id']
                                ]));
                                $job = new StockFillJob();
                                $job->mall_id = $mall->id;
                                $job->remain_num = $total_num;
                                $job->user_id = $item['user_id'];
                                $job->queue_id = $id;
                                $job->buy_user_id = $order->user_id;
                                $job->goods_id = $order->goods_id;
                                $job->unit_price = $unit_price;
                                $job->common_order_detail_id = $this->common_order_detail_id;
                                $job->fill_end_time = time() + $temp_fill_time * 60 * 60;
                                if (!$job->save()) {
                                    \Yii::error("StockLogJob execute stockFillJob save error=".json_encode($job->getErrors()));
                                }else{
                                    \Yii::error("StockLogJob execute StockFillJob save sendFillNoticeSms temp_fill_time={$temp_fill_time}");
                                    Common::sendFillNoticeSms($item['user_id'],$temp_fill_time,$order->goods_id,$remain_num,$order->user_id);
                                    return;
                                }
                            }
                        }
                    }
                }
            }
        }
        //这里是订单状态改变,已完成
        else if ($this->type == 2) {
            \Yii::warning('*******************处理已完成订单的队列***********************');
            StockPriceLog::updateAll(['status' => $this->order->status], ['common_order_detail_id' => $this->common_order_detail_id, 'status' => 0]);
            //有效
            $log_list = StockPriceLog::find()->andWhere(['common_order_detail_id' => $this->common_order_detail_id, 'is_delete' => 0, 'is_price' => 0])->all();
            if ($this->order->status == 1) {
                //符合分润条件
//                if ($compute_time) {
//                    $compute_time = floatval($compute_time);
//                    \Yii::$app->queue->delay($compute_time * 60 * 60)->push(new StockPriceJob(['common_order_detail_id' => $this->common_order_detail_id]));
//                } else {
//                    \Yii::$app->queue->delay(24 * 60 * 60)->push(new StockPriceJob(['common_order_detail_id' => $this->common_order_detail_id]));
//                }
                $compute_time = $compute_time > 0 ? floatval($compute_time) : 0;
                \Yii::$app->queue->delay($compute_time * 60 * 60)->push(new StockPriceJob(['common_order_detail_id' => $this->common_order_detail_id]));
                $this->agentUpgrade($order, $mall, 0);
            }
            foreach ($log_list as $log) {
                /**
                 * @var StockPriceLog $log
                 */
                if ($this->order->status == -1) {
                    //不符合 库存返还
                    \Yii::warning('=============================库存返还============================');

                    $stock_agent_goods = StockAgentGoods::findOne(['goods_id' => $log->goods_id, 'user_id' => $log->user_id, 'is_delete' => 0]);
                    if ($stock_agent_goods) {
                        \Yii::warning('=============================库存返还之前============================'.$stock_agent_goods->num);
                        $stock_agent_goods->num += $log->num;
                        $stock_agent_goods->save();
                        \Yii::warning('=============================库存返还之后============================'.$stock_agent_goods->num);
                    }
                }
            }
            unset($log);
        }
        else if ($this->type == 3) {
            $this->agentUpgrade($order, $mall, 1);
        }
    }

    private function agentUpgrade(CommonOrderDetail $order, $mall, $compute_type = 0)
    {
        if ($order->goods_type == CommonOrderDetail::TYPE_MALL_GOODS) {
            \Yii::warning('进入到升级 compute_type = '.$compute_type);
            $bag = UpgradeBag::findOne(['mall_id' => $mall->id, 'compute_type' => $compute_type, 'is_enable' => 1, 'goods_id' => $order->goods_id]);
            \Yii::warning('StockLogJob agentUpgrade bag = '.var_export($bag,true));
            if ($bag) {
                $log = new UpgradeBagLog();
                $log->mall_id = $mall->id;
                $log->user_id = $order->user_id;
                $log->common_order_detail_id = $this->common_order_detail_id;
                $log->bag_id = $bag->id;
                $log->save();
                $stock_agent = StockAgent::findOne(['user_id' => $order->user_id, 'is_delete' => 0]);
                \Yii::warning('StockLogJob agentUpgrade stock_agent = '.var_export($stock_agent,true));
                if ($stock_agent) {
                    if ($stock_agent->level < $bag->level) {
                        $stock_agent->level = $bag->level;
                        $stock_agent->upgrade_status = StockAgent::UPGRADE_STATUS_GOODS;
                        $stock_agent->upgrade_level_at = time();
                        if ($stock_agent->save()) {
                            if ($bag->is_stock) {
                                $goods = Goods::findOne(['is_delete' => 0, 'id' => $bag->stock_goods_id]);
                                if (!$goods) {
                                    \Yii::warning('商品不存在');
                                    return;
                                }
                                $stock_agent_goods = StockAgentGoods::findOne(['user_id' => $order->user_id, 'goods_id' => $bag->stock_goods_id, 'is_delete' => 0]);
                                \Yii::warning('StockLogJob agentUpgrade stock_agent_goods = '.var_export($stock_agent_goods,true));
                                if (!$stock_agent_goods) {
                                    $stock_agent_goods = new StockAgentGoods();
                                    $stock_agent_goods->mall_id = $mall->id;
                                    $stock_agent_goods->user_id = $order->user_id;
                                    $stock_agent_goods->goods_id = $bag->stock_goods_id;
                                    $stock_agent_goods->num = $bag->stock_num;
                                } else {
                                    $stock_agent_goods->num += $bag->stock_num;
                                }
                                $stock_agent_goods->sale_price = $goods->price;
                                $stock_agent_goods->save();
                            }
                        } else {
                            \Yii::warning('代理商升级失败' . json_encode($stock_agent->getErrors()));
                        }
                    }
                } else {
                    \Yii::warning('StockLogJob agentUpgrade else start');
                    $stock_agent = new StockAgent();
                    $stock_agent->mall_id = $mall->id;
                    $stock_agent->user_id = $order->user_id;
                    $stock_agent->level = $bag->level;
                    $stock_agent->upgrade_status = StockAgent::UPGRADE_STATUS_GOODS;
                    $stock_agent->upgrade_level_at = time();
                    if ($stock_agent->save()) {
                        \Yii::warning('StockLogJob agentUpgrade else stock_agent success');
                        if ($bag->is_stock) {
                            $goods = Goods::findOne(['is_delete' => 0, 'id' => $bag->stock_goods_id]);
                            if (!$goods) {
                                \Yii::warning('商品不存在');
                                return;
                            }
                            $stock_agent_goods = StockAgentGoods::findOne(['user_id' => $order->user_id, 'goods_id' => $bag->stock_goods_id, 'is_delete' => 0]);
                            \Yii::warning('StockLogJob agentUpgrade else stock_agent_goods = '.var_export($stock_agent_goods,true));
                            if (!$stock_agent_goods) {
                                $stock_agent_goods = new StockAgentGoods();
                                $stock_agent_goods->mall_id = $mall->id;
                                $stock_agent_goods->user_id = $order->user_id;
                                $stock_agent_goods->goods_id = $bag->stock_goods_id;
                                $stock_agent_goods->num = $bag->stock_num;
                            } else {
                                $stock_agent_goods->num += $bag->stock_num;
                            }
                            $stock_agent_goods->sale_price = $goods->price;
                            $stock_agent_goods->save();
                        }
                    } else {
                        \Yii::warning('StockLogJob 代理商升级失败' . json_encode($stock_agent->getErrors()));
                    }
                }
            }
        }
    }
}