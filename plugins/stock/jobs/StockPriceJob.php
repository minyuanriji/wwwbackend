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
use app\plugins\stock\models\GoodsPriceLog;
use app\plugins\stock\models\StockAgent;
use app\plugins\stock\models\StockPriceLog;
use yii\base\Component;
use yii\queue\JobInterface;
use yii\queue\Queue;

/**
 * Class StockPriceJob
 * @package app\plugins\stock\jobs
 * @Notes 分佣队列
 */
class StockPriceJob extends Component implements JobInterface
{
    /** @var CommonOrderDetail $order */
    public $order;
    public $common_order_detail_id;
    /**
     *
     * @param Queue $queue
     * @return mixed|void
     * @throws \Exception
     */

    //TODO 还需要加入其他筛选添加 例如是商城商品还是其他商品
    public function execute($queue)
    {
        \Yii::warning('stockPriceJob start 云库存结算记录队列开始执行');
        $order = CommonOrderDetail::findOne(['status' => 1, 'id' => $this->common_order_detail_id]);
        if (!$order) {
            \Yii::warning('stockPriceJob 公共订单不存在');
            return;
        }
        $mall = Mall::findOne($order->mall_id);
        if (!$mall) {
            \Yii::warning('stockPriceJob 找不到商城');
            return;
        }
        \Yii::$app->setMall($mall);
        $total_price = PriceLog::find()->where(['common_order_detail_id' => $this->common_order_detail_id, 'status' => 1])->sum('price');
        $total_price = $total_price ?? 0;
        if ($total_price && $order->num) {
            $unit_price = floatval($total_price) / $order->num;
        } else {
            $unit_price = 0;
        }
        $log_list = StockPriceLog::find()->where(['common_order_detail_id' => $this->common_order_detail_id, 'is_delete' => 0, 'status' => 1])->all();
        foreach ($log_list as $log) {
            /**
             * @var StockPriceLog $log
             */
            $log->income = $log->price - $unit_price * $log->num;
            $log->income = $log->income >= 0 ? $log->income : 0;
            $log->is_price = 1;
            if (!$log->save()) {
                \Yii::warning(json_encode($log->getErrors()));
            }
            //保存成功之后要减掉冻结的钱
            $user = User::findOne($log->user_id);
            \Yii::$app->currency->setUser($user)->income
                ->add(floatval($log->income), "云库存结算ID：{$log->id} 的佣金发放", 0, 1);
            $stock_agent = StockAgent::findOne(['user_id' => $log->user_id, 'is_delete' => 0]);
            if ($stock_agent) {
                \Yii::warning('stockPriceJob 找到代理商');
                $stock_agent->total_price += floatval($log->income);
                if (!$stock_agent->save()) {
                    \Yii::warning(SerializeHelper::encode($stock_agent));
                }
                $log1 = new GoodsPriceLog();
                $log1->mall_id = $stock_agent->mall_id;
                $log1->user_id = $stock_agent->user_id;
                $log1->price = $log->income;
                $log1->goods_id = $log->goods_id;
                $log1->log_id = $log->id;
                $log1->order_no = $order->order_no;
                $log1->buy_user_id = $order->user_id;
                $log1->type = 0;
                $log1->save();
            } else {
                \Yii::warning('stockPriceJob 找不到代理商');
            }
        }
    }
}