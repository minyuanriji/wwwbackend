<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * Created by PhpStorm
 * Author: ganxiaohao
 * Date: 2020-07-12
 * Time: 9:49
 */

namespace app\plugins\stock\jobs;


use app\helpers\SerializeHelper;
use app\logic\CommonLogic;
use app\models\Goods;
use app\models\GoodsAttr;
use app\models\Mall;
use app\models\User;
use app\plugins\stock\models\FillOrder;
use app\plugins\stock\models\FillOrderDetail;
use app\plugins\stock\models\StockAgent;
use app\plugins\stock\models\StockAgentGoods;
use app\plugins\stock\models\StockGoods;
use yii\base\BaseObject;
use yii\db\Exception;
use yii\queue\JobInterface;
use yii\queue\Queue;

class FillOrderPayedJob extends BaseObject implements JobInterface
{
    /**
     * @var FillOrder $order
     */
    public $order;
    /**
     * @param Queue $queue which pushed and is handling the job
     * @return void|mixed result of the job execution
     */
    public function execute($queue)
    {
        $order_detail_list = FillOrderDetail::find()->where(['order_id' => $this->order->id, 'is_delete' => 0, 'is_give' => 0, 'mall_id' => $this->order->mall_id])->all();
        $stock_agent = StockAgent::findOne(['user_id' => $this->order->user_id, 'is_delete' => 0]);
        if (!$stock_agent) {
            \Yii::warning('用户不是代理商');
            return;
        }
        \Yii::warning('FillOrderPayedJob execute order_detail_list='.var_export($order_detail_list,true));
        $mall = Mall::findOne($this->order->mall_id);
        \Yii::$app->setMall($mall);
        $transaction = \Yii::$app->db->beginTransaction();
        try{
            foreach ($order_detail_list as $detail) {
                /**
                 * @var FillOrderDetail $detail
                 */
                $goods_id = $detail->goods_id;
                $agent_goods = StockAgentGoods::findOne(['goods_id' => $detail->goods_id, 'user_id' => $this->order->user_id, 'mall_id' => $this->order->mall_id, 'is_delete' => 0]);
                if (!$agent_goods) {
                    $agent_goods = new StockAgentGoods();
                    $agent_goods->mall_id = $this->order->mall_id;
                    $agent_goods->user_id = $this->order->user_id;
                    $agent_goods->goods_id = $goods_id;
                }
                $agent_goods->sale_price = $detail->sale_price;
                $agent_goods->num += $detail->num;
                $result = $agent_goods->save();
                if($result === false){
                    throw new \Exception("用户的云库存添加失败");
                }

                $detail->is_give = 1;
                try{
                    $stock_goods = StockGoods::findOne(['goods_id' => $detail->goods_id, 'is_delete' => 0, 'mall_id' => $this->order->mall_id]);
                    $price = 0;
                    if ($stock_goods) {
                        $fill_level_list = $stock_goods->fill_level_list;
                        if (!empty($fill_level_list) && $fill_level_list != "null") {
                            $fill_level_list = SerializeHelper::decode($fill_level_list);
                        } else {
                            $fill_level_list = [];
                        }
                        foreach ($fill_level_list as $level) {
                            if ($level['level'] == $stock_agent->level) {
                                $price = floatval($level['fill_price']);
                                break;
                            }
                        }
                    }
                    if ($price) {
                        $detail->fill_price = $price * intval($detail->num);
                    }
                    $result = $detail->save();
                    if($result === false){
                        \Yii::error('FillOrderPayedJob execute detail save='.SerializeHelper::encode($detail->getErrors()));
                        throw new \Exception("补货订单详情更新失败");
                    }
                }catch (\Exception $e){
                    \Yii::error("FillOrderPayedJob foreach execute error:".CommonLogic::getExceptionMessage($e));
                    throw new \Exception(CommonLogic::getExceptionMessage($e));
                }
                if (!empty($detail->fill_price)) {
                    //保存成功之后要减掉冻结的钱
                    $user = User::findOne($stock_agent->user_id);
                    if ($stock_agent) {
                        $stock_agent->total_price += floatval($detail->fill_price);
                        if (!$stock_agent->save()) {
                            \Yii::error("FillOrderPayedJob execute  stock_agent getErrors=".SerializeHelper::encode($stock_agent->getErrors()));
                            throw new \Exception(SerializeHelper::encode($stock_agent->getErrors()));
                        }
                        \Yii::$app->currency->setUser($user)->income
                            ->add(floatval($detail->fill_price), "代理商补货奖励记录ID：{$detail->id} 的佣金发放", 0, 1,0,1);
                    } else {
                        \Yii::warning('FillOrderPayedJob execute 找不到代理商');
                    }
                }
            }
            $transaction->commit();
        }catch (\Exception $ex){
            $transaction->rollBack();
            \Yii::error("FillOrderPayedJob error ".CommonLogic::getExceptionMessage($ex));
        }
        \Yii::warning('FillOrderPayedJob execute end');
    }
}