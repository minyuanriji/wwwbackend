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

use app\models\GoodsAttr;
use app\models\Mall;
use app\models\UserParent;
use app\plugins\stock\forms\common\Common;
use app\plugins\stock\models\FillOrder;
use app\plugins\stock\models\FillOrderDetail;
use app\plugins\stock\models\FillPriceLog;
use app\plugins\stock\models\StockAgent;
use app\plugins\stock\models\StockAgentGoods;
use app\plugins\stock\models\StockSetting;
use yii\base\Component;
use yii\queue\JobInterface;
use yii\queue\Queue;

class FillOrderSubmitJob extends Component implements JobInterface
{

    public $order_id;
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
        \Yii::warning('FillOrderSubmitJob 进入补货订单提交队列中');
        $order = FillOrder::findOne(['is_delete' => 0, 'mall_id' => $this->mall_id, 'id' => $this->order_id]);
        if (!$order) {
            \Yii::warning('订单不存在！');
            return;
        }
        $agent = StockAgent::findOne(['user_id' => $order->user_id, 'is_delete' => 0]);
        if (!$agent) {
            \Yii::warning('购买者不是代理商');
            return;
        }
        $mall = Mall::findOne($agent->mall_id);
        if (!$mall) {
            \Yii::warning('AgentFillJob 找不到商城');
            return;
        }
        \Yii::$app->setMall($mall);
        $is_allow_temp_fill = StockSetting::getValueByKey(StockSetting::IS_ALLOW_TEMP_FILL, $this->mall_id);
        $temp_fill_time = StockSetting::getValueByKey(StockSetting::TEMP_FILL_TIME, $this->mall_id);
        try {
            $agent_list = StockAgent::find()->alias('sa')
                ->leftJoin(['up' => UserParent::tableName()], 'up.parent_id=sa.user_id')
                ->where(['up.user_id' => $order->user_id, 'up.is_delete' => 0])
                ->andWhere(['>', 'sa.level', $agent->level])
                ->orderBy('up.level ASC')
                ->select('sa.id,sa.user_id')
                ->asArray()
                ->all();
            \Yii::warning('FillOrderSubmitJob agent_list='.var_export($agent_list,true));
        } catch (\Exception $e) {
            \Yii::warning("FillOrderSubmitJob exception error".$e->getMessage());
        }
        $order_detail_list = FillOrderDetail::find()->where(['order_id' => $this->order_id, 'is_delete' => 0])->all();
        \Yii::warning('FillOrderSubmitJob order_detail_list='.var_export($order_detail_list,true));
        foreach ($order_detail_list as $detail) {
            /**
             * @var FillOrderDetail $detail
             *
             */
            $total_num = $detail->num;
            $unit_price = floatval($detail->price) / intval($detail->num);
            if(!empty($agent_list)){
                foreach ($agent_list as $agent) {
                    if ($total_num <= 0) {
                        break;
                    }
                    $agent_goods = StockAgentGoods::findOne(['goods_id' => $detail->goods_id, 'is_delete' => 0, 'user_id' => $agent['user_id']]);
                    \Yii::warning('FillOrderSubmitJob agent_goods='.var_export($agent_goods,true));
                    if ($agent_goods && $agent_goods->num > 0) {
                        if ($agent_goods->num >= $total_num) {
                            $log = new FillPriceLog();
                            $log->mall_id = $this->mall_id;
                            $log->price = $detail->price;
                            $log->num = $detail->num;
                            $log->goods_id = $detail->goods_id;
                            $log->fill_order_detail_id = $detail->id;
                            $log->user_id = $agent_goods->user_id;
                            $log->order_id = $this->order_id;
                            if (!$log->save()) {
                                \Yii::warning('保存失败' . json_encode($log->getErrors()));
                            }
                            $agent_goods->num -= $detail->num;
                            $total_num = 0;
                            $agent_goods->save();
                        } else {
                            $log = new FillPriceLog();
                            $log->mall_id = $this->mall_id;
                            $log->price = $unit_price * intval($agent_goods->num);
                            $log->num = $agent_goods->num;
                            $log->goods_id = $detail->goods_id;
                            $log->fill_order_detail_id = $detail->id;
                            $log->user_id = $agent_goods->user_id;
                            $log->order_id = $this->order_id;
                            if (!$log->save()) {
                                \Yii::warning('保存失败' . json_encode($log->getErrors()));
                            }
                            $total_num = $total_num - $agent_goods->num;
                            $agent_goods->num = 0;
                            $agent_goods->save();
                        }
                    }
                    \Yii::warning('FillOrderSubmitJob total_num='.$total_num);
                    if ($is_allow_temp_fill && $total_num != 0) {
                        \Yii::warning('FillOrderSubmitJob 不够货需要去补货');
                        $id = \Yii::$app->queue
                            ->delay($temp_fill_time * 60 * 60)
                            ->push(new AgentFillJob([
                                'user_id' => $agent['user_id'],
                                'fill_order_detail_id' => $detail->id,
                                'mall_id' => $this->mall_id,
                                'unit_price' => $unit_price,
                                'remain_num' => $total_num,
                                'goods_id' => $detail->goods_id,
                                'order_id'=>$this->order_id
                            ]));
                        $job = new \app\plugins\stock\models\AgentFillJob();
                        $job->mall_id = $this->mall_id;
                        $job->remain_num = $total_num;
                        $job->user_id = $agent['user_id'];
                        $job->queue_id = $id;
                        $job->goods_id = $detail->goods_id;
                        $job->unit_price = $unit_price;
                        $job->fill_order_detail_id = $detail->id;
                        $job->fill_end_time = time() + $temp_fill_time * 60 * 60;
                        if (!$job->save()) {
                            \Yii::warning("FillOrderSubmitJob AgentFillJob save error ".json_encode($job->getErrors()));
                        }else{
                            \Yii::error("FillOrderSubmitJob execute AgentFillJob save sendFillNoticeSms temp_fill_time={$temp_fill_time}");
                            Common::sendFillNoticeSms($agent['user_id'],$temp_fill_time,$detail->goods_id,$total_num,$order->user_id);
                            return;
                        }
                        break;
                    }
                }
            }
            else{
                //没有记录，扣平台库存
                Common::updateGoodsStock($detail->goods_id,0,$detail->num,$outIn = 2);
            }

        }
    }
}