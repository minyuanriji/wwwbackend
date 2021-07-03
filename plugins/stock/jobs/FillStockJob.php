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
use app\models\Mall;
use app\models\PriceLog;
use app\models\User;
use app\models\UserChildren;
use app\models\UserParent;
use app\plugins\stock\forms\common\Common;
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

class FillStockJob extends Component implements JobInterface
{
    //剩余数量
    public $remain_num;
    //总数量
    public $total_num;
    //公共订单ID
    public $common_order_detail_id;
    //单价
    public $unit_price;
    //购买者ID
    public $user_id;
    //需要补货的人ID
    public $stock_user_id;
    public $goods_id;
    public $mall_id;
    /**
     *
     * @param Queue $queue
     * @return mixed|void
     * @throws \Exception
     */
    //TODO 还需要加入其他筛选添加 例如是商城商品还是其他商品
    public function execute($queue)
    {
        \Yii::warning('FillStockJob execute start');
        \Yii::warning('FillStockJob execute remain_num='.$this->remain_num.";common_order_detail_id:".$this->common_order_detail_id.
                            "unit_price=".$this->unit_price.";user_id=".$this->user_id.";stock_user_id=".$this->stock_user_id.";goods_id=".$this->goods_id);
        $order = CommonOrderDetail::findOne($this->common_order_detail_id);
        \Yii::warning('FillStockJob execute order='.var_export($order,true));
        if (!$order) {
            \Yii::warning("--- FillStockJob execute 公共订单不存在：{$this->common_order_detail_id}  商城ID{$order->mall_id}---");
            return;
        }
        $mall = Mall::findOne($order->mall_id);
        if (!$mall) {
            \Yii::warning('AgentFillJob 找不到商城');
            return;
        }
        \Yii::$app->setMall($mall);
        $is_allow_temp_fill = StockSetting::getValueByKey(StockSetting::IS_ALLOW_TEMP_FILL, $this->mall_id);
        $temp_fill_time = StockSetting::getValueByKey(StockSetting::TEMP_FILL_TIME, $this->mall_id);
        $stock_agent_goods = StockAgentGoods::findOne(['user_id' => $this->stock_user_id, 'is_delete' => 0, 'goods_id' => $this->goods_id]);
        \Yii::warning('FillStockJob execute stock_agent_goods='.var_export($stock_agent_goods,true));
        if ($stock_agent_goods && $stock_agent_goods->num >= $this->remain_num) {
            \Yii::warning('FillStockJob execute 看看补货的够不够');
            $log = new StockPriceLog();
            $log->num = $this->remain_num;
            $log->price = intval($this->remain_num) * floatval($this->unit_price);
            $log->user_id = $this->stock_user_id;
            $log->goods_id = $this->goods_id;
            $log->mall_id = $this->mall_id;
            $log->common_order_detail_id = $this->common_order_detail_id;
            $log->save();
            $stock_agent_goods->num -= $this->remain_num;
            $stock_agent_goods->save();
            \Yii::warning('FillStockJob execute stock_agent_goods->num='.$stock_agent_goods->num );
        } else {
            \Yii::warning('未来得及补货，往上面走');
            $user_parent_list = UserParent::find()->alias('up')
                ->leftJoin(['sa' => StockAgent::tableName()], 'sa.user_id=up.parent_id')
                ->where(['up.user_id' => $this->stock_user_id, 'up.is_delete' => 0, 'sa.is_delete' => 0])
                ->select('sa.user_id,sa.id')
                ->orderBy('up.level ASC')
                ->asArray()
                ->all();
            \Yii::warning('FillStockJob execute user_parent_list='.var_export($user_parent_list,true) );
            $total_num = $this->total_num;
            $remain_num = $this->remain_num;
            //没有上级了,扣平台库存，因为一下单就扣了平台库存，所以这里需要把库存还回去
            if(count($user_parent_list) == 0){
                Common::updateGoodsStock($this->goods_id,$order->order_detail_id,intval($remain_num),$outIn = 2);
                return;
            }
            foreach ($user_parent_list as $item) {
                if ($remain_num <= 0) {
                    break;
                }
                $agent_goods = StockAgentGoods::findOne(['goods_id' => $this->goods_id,'is_delete'=>0, 'user_id' => $item['user_id']]);
                \Yii::warning('FillStockJob execute agent_goods：'.var_export($agent_goods,true));
                if ($agent_goods) {
                    if ($agent_goods->num >= $remain_num) {
                        $log = new StockPriceLog();
                        $log->num = $remain_num;
                        $log->price = intval($remain_num) * floatval($this->unit_price);
                        $log->user_id = $item['user_id'];
                        $log->mall_id = $this->mall_id;
                        $log->goods_id = $this->goods_id;
                        $log->common_order_detail_id = $this->common_order_detail_id;
                        if (!$log->save()) {
                            \Yii::error("FillStockJob execute stockFillJob save error=".json_encode($log->getErrors()));
                        }
                        $agent_goods->num -= $remain_num;
                        $agent_goods->save();
                        \Yii::warning('FillStockJob execute agent_goods->num：'.$agent_goods->num);
                        return;
                    }
                    if ($agent_goods->num < $remain_num) {
                        if ($agent_goods->num > 0) {
                            $log = new StockPriceLog();
                            $log->num = intval($agent_goods->num);
                            $log->price = intval($agent_goods->num) * floatval($this->unit_price);
                            $log->user_id = $item['user_id'];
                            $log->goods_id = $this->goods_id;
                            $log->mall_id = $this->mall_id;
                            $log->common_order_detail_id = $this->common_order_detail_id;
                            $log->save();
                            $remain_num -= intval($agent_goods->num);
                            $agent_goods->num = 0;
                            $result = $agent_goods->save();
                            \Yii::warning('FillStockJob execute result：'.$result);
                        }
                    }
                }
                if($remain_num != 0){
                    //库存不足的时候
                    if ($is_allow_temp_fill) { //允许补货
                        \Yii::warning('FillStockJob execute 创建补货队列2');
                        $id = \Yii::$app->queue->delay($temp_fill_time * 60 * 60)->push(new FillStockJob([
                            'user_id' => $this->user_id,
                            'common_order_detail_id' => $this->common_order_detail_id,
                            'mall_id' => $this->mall_id,
                            'unit_price' => $this->unit_price,
                            'remain_num' => $remain_num,
                            'total_num' => $total_num,
                            'goods_id' => $this->goods_id,
                            'stock_user_id' => $item['user_id']
                        ]));
                        $job = new StockFillJob();
                        $job->mall_id = $this->mall_id;
                        $job->remain_num = $remain_num;
                        $job->user_id = $item['user_id'];
                        $job->queue_id = $id;
                        $job->buy_user_id = $this->user_id;
                        $job->goods_id = $this->goods_id;
                        $job->unit_price = $this->unit_price;
                        $job->common_order_detail_id = $this->common_order_detail_id;
                        $job->fill_end_time=time()+$temp_fill_time * 60 * 60;
                        if (!$job->save()) {
                            \Yii::error("FillStockJob execute stockFillJob save error=".json_encode($job->getErrors()));
                        }else{
                            \Yii::error("FillStockJob execute stockFillJob save sendFillNoticeSms temp_fill_time={$temp_fill_time}");
                            Common::sendFillNoticeSms($item['user_id'],$temp_fill_time,$this->goods_id,$remain_num,$this->user_id);
                            return;
                        }
                    }
                }
            }
        }
    }
}