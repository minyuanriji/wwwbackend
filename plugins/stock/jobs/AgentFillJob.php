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

use app\helpers\SerializeHelper;
use app\models\CommonOrderDetail;
use app\models\GoodsAttr;
use app\models\Mall;
use app\models\PriceLog;
use app\models\User;
use app\models\UserChildren;
use app\models\UserParent;
use app\plugins\stock\forms\common\Common;
use app\plugins\stock\models\FillPriceLog;
use app\plugins\stock\models\Stock;
use app\plugins\stock\models\StockAgent;
use app\plugins\stock\models\StockAgentGoods;
use app\plugins\stock\models\StockFillJob;
use app\plugins\stock\models\StockGoods;
use app\plugins\stock\models\StockGoodsDetail;
use app\plugins\stock\models\StockLevel;
use app\plugins\stock\models\StockPriceLog;
use app\plugins\stock\models\StockPriceLogType;
use app\plugins\stock\models\StockSetting;
use app\plugins\stock\Plugin;
use yii\base\Component;
use yii\base\Exception;
use yii\queue\JobInterface;
use yii\queue\Queue;


/**
 * Class AgentFillJob
 * @package app\plugins\stock\jobs
 * @Notes 代理商补货的队列
 */
class AgentFillJob extends Component implements JobInterface
{
    //剩余数量
    public $remain_num;
    //公共订单ID
    public $fill_order_detail_id;
    //单价
    public $unit_price;
    //需要补货的人的ID
    public $user_id;
    //需要补货的人ID
    public $goods_id;
    public $mall_id;
    public $order_id;
    /**
     *
     * @param Queue $queue
     * @return mixed|void
     * @throws \Exception
     */
    //TODO 还需要加入其他筛选添加 例如是商城商品还是其他商品
    public function execute($queue)
    {
        \Yii::warning("----- AgentFillJob start ----");
        \Yii::warning("AgentFillJob user_id = ".$this->user_id.";fill_order_detail_id={$this->fill_order_detail_id}");
        $is_allow_temp_fill = StockSetting::getValueByKey(StockSetting::IS_ALLOW_TEMP_FILL, $this->mall_id);
        $temp_fill_time = StockSetting::getValueByKey(StockSetting::TEMP_FILL_TIME, $this->mall_id);
        $stock_agent = StockAgent::findOne(['user_id' => $this->user_id, 'is_delete' => 0]);
        if (!$stock_agent) {
            \Yii::warning('不是代理商');
            return;
        }
        $mall = Mall::findOne($stock_agent->mall_id);
        if (!$mall) {
            \Yii::warning('AgentFillJob 找不到商城');
            return;
        }
        \Yii::$app->setMall($mall);
        $stock_agent_goods = StockAgentGoods::findOne(['user_id' => $this->user_id, 'is_delete' => 0, 'goods_id' => $this->goods_id]);
        \Yii::warning("AgentFillJob stock_agent_goods = ".var_export($stock_agent_goods,true));
        if ($stock_agent_goods && $stock_agent_goods->num >= $this->remain_num) {
            $log = new FillPriceLog();
            $log->num = $this->remain_num;
            $log->price = intval($this->remain_num) * floatval($this->unit_price);
            $log->user_id = $this->user_id;
            $log->goods_id = $this->goods_id;
            $log->mall_id = $this->mall_id;
            $log->fill_order_detail_id = $this->fill_order_detail_id;
            $log->order_id=$this->order_id;
            $log->save();
            $stock_agent_goods->num -= $this->remain_num;
            $stock_agent_goods->save();
        } else {
            $user_parent_list = UserParent::find()->alias('up')
                ->leftJoin(['sa' => StockAgent::tableName()], 'sa.user_id=up.parent_id')
                ->where(['up.user_id' => $this->user_id, 'up.is_delete' => 0, 'sa.is_delete' => 0])
                ->andWhere(['>', 'sa.level', $stock_agent->level])
                ->select('sa.user_id,sa.id')
                ->orderBy('up.level ASC')
                ->asArray()
                ->all();
            $total_num = $this->remain_num;
            \Yii::warning("AgentFillJob user_parent_list = ".var_export($user_parent_list,true));
            //没有上级了,扣平台库存
            if(count($user_parent_list) == 0){
                Common::updateGoodsStock($this->goods_id,0,$this->remain_num,$outIn = 2);
                return;
            }

            $isSubStock = 0;
            foreach ($user_parent_list as $item) {
                if ($total_num <= 0) {
                    break;
                }
                $agent_goods = StockAgentGoods::findOne(['goods_id' => $this->goods_id, 'user_id' => $item['user_id']]);
                if(!empty($agent_goods)){
                    if ($agent_goods->num >= $total_num) {
                        $log = new FillPriceLog();
                        $log->num = $total_num;
                        $log->price = intval($total_num) * floatval($this->unit_price);
                        $log->user_id = $item['user_id'];
                        $log->mall_id = $this->mall_id;
                        $log->goods_id = $this->goods_id;
                        $log->fill_order_detail_id = $this->fill_order_detail_id;
                        $log->order_id=$this->order_id;
                        if (!$log->save()) {
                            \Yii::warning(json_encode($log->getErrors()));
                        }
                        $agent_goods->num -= $total_num;
                        $agent_goods->save();
                    }
                    else if ($agent_goods->num < $total_num) {
                        if ($agent_goods->num > 0) {
                            $log = new FillPriceLog();
                            $log->num = intval($agent_goods->num);
                            $log->price = intval($agent_goods->num) * floatval($this->unit_price);
                            $log->user_id = $item['user_id'];
                            $log->goods_id = $this->goods_id;
                            $log->mall_id = $this->mall_id;
                            $log->order_id=$this->order_id;
                            $log->fill_order_detail_id = $this->fill_order_detail_id;
                            $log->save();
                            $total_num -= intval($agent_goods->num);
                            $agent_goods->num = 0;
                            $agent_goods->save();
                        }
                        //库存不足的时候
                        if ($is_allow_temp_fill) { //允许补货
                            \Yii::warning('AgentFillJob 代理商补货队列');
                            $id = \Yii::$app->queue->delay($temp_fill_time * 60 * 60)->push(new AgentFillJob([
                                'user_id' => $item['user_id'],
                                'fill_order_detail_id' => $this->fill_order_detail_id,
                                'mall_id' => $this->mall_id,
                                'unit_price' => $this->unit_price,
                                'remain_num' => $total_num,
                                'goods_id' => $this->goods_id
                            ]));
                            $job = new \app\plugins\stock\models\AgentFillJob();
                            $job->mall_id = $this->mall_id;
                            $job->remain_num = $total_num;
                            $job->user_id = $item['user_id'];
                            $job->queue_id = $id;
                            $job->goods_id = $this->goods_id;
                            $job->unit_price = $this->unit_price;
                            $job->fill_order_detail_id = $this->fill_order_detail_id;
                            $job->fill_end_time = time() + $temp_fill_time * 60 * 60;
                            if (!$job->save()) {
                                \Yii::warning("AgentFillJob execute save error ".json_encode($job->getErrors()));
                            }else{
                                \Yii::error("AgentFillJob execute AgentFillJob save sendFillNoticeSms temp_fill_time={$temp_fill_time}");
                                Common::sendFillNoticeSms($item['user_id'],$temp_fill_time,$this->goods_id,$total_num,$this->user_id);
                                return;
                            }
                        }
                    }
                    $isSubStock++;
                }
            }
            //上级都没有该库存商品，则直接扣平台库存
            if($isSubStock == 0){
                Common::updateGoodsStock($this->goods_id,0,$this->remain_num,$outIn = 2);
                return;
            }
        }
    }
}