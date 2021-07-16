<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * Created by PhpStorm
 * Author: ganxiaohao
 * Date: 2020-07-30
 * Time: 15:24
 */

namespace app\plugins\stock\jobs;


use app\helpers\SerializeHelper;
use app\models\Mall;
use app\models\User;
use app\models\UserParent;
use app\plugins\stock\models\FillIncomeLog;
use app\plugins\stock\models\FillOrder;
use app\plugins\stock\models\FillOrderDetail;
use app\plugins\stock\models\FillPriceLog;
use app\plugins\stock\models\GoodsPriceLog;
use app\plugins\stock\models\StockAgent;
use app\plugins\stock\models\StockGoods;
use app\plugins\stock\models\StockLevel;
use app\plugins\stock\models\StockSetting;
use yii\base\Component;
use yii\db\Exception;
use yii\queue\JobInterface;
use yii\queue\Queue;

class FillOrderPriceLogJob extends Component implements JobInterface
{

    /**
     * @var FillOrder
     */
    public $order;
    public $mall_id;

    /**
     * @param Queue $queue which pushed and is handling the job
     * @return void|mixed result of the job execution
     */
    public function execute($queue)
    {
        \Yii::warning("FillOrderPriceLogJob ------ start ----------");
        \Yii::warning("FillOrderPriceLogJob order = ".var_export($this->order,true));
        //越级奖还没写
        $buy_user_id = $this->order->user_id;
        // TODO: Implement execute() method.
        $this->mall_id = $this->order->mall_id;
        $mall = Mall::findOne($this->mall_id);
        if (!$mall) {
            return;
        }
        \Yii::$app->setMall($mall);
        $is_enable = StockSetting::getValueByKey(StockSetting::IS_ENABLE, $this->mall_id); //启用云库存
        $equal_level = StockSetting::getValueByKey(StockSetting::EQUAL_LEVEL, $this->mall_id);//平级层数
        $stockAgent = StockAgent::findOne(['user_id' => $this->order->user_id, 'is_delete' => 0]);
        \Yii::warning("FillOrderPriceLogJob equal_level = ".$equal_level.";stockAgent=".var_export($stockAgent,true));
        if ($this->order->is_pay == 1) {
            \Yii::warning('订单已经支付');
            if ($stockAgent) {
                //查询云库存商品补货订单记录
                $order_detail_list = FillOrderDetail::find()->alias('d')
                    ->leftJoin(['g' => StockGoods::tableName()], 'g.goods_id=d.goods_id')
                    ->where(['d.order_id' => $this->order->id, 'd.is_delete' => 0])
                    ->select('d.*,g.over_level_list,g.equal_level_list')
                    ->asArray()
                    ->all();
                \Yii::warning("FillOrderPriceLogJob is_pay order_detail_list=".var_export($order_detail_list,true));
                foreach ($order_detail_list as $item) {
                    $price = 0;
                    //计算越级奖
                    if (!empty($item['over_level_list']) && $item['over_level_list'] != "null") {
                        //查询比当前下单用户等级低的用户
                        $userParent = UserParent::find()->alias('up')
                            ->leftJoin(['sa' => StockAgent::tableName()], 'sa.user_id=up.parent_id')
                            ->where(['up.user_id' => $this->order->user_id, 'up.is_delete' => 0])
                            ->andWhere(["<","sa.level",$stockAgent->level])
                            ->select('sa.user_id,sa.level')
                            ->orderBy('up.level ASC')
                            ->asArray()->one();
                        \Yii::warning("FillOrderPriceLogJob is_pay userParent=".var_export($userParent,true));
                        $over_level_list = SerializeHelper::decode($item['over_level_list']);
                        \Yii::warning("FillOrderPriceLogJob is_pay over_level_list=".var_export($over_level_list,true));
                        if(!empty($userParent)){
                            foreach ($over_level_list as $level) {
                                if ($level['level'] == $userParent['level']) {
                                    //是否开启了该等级的越级奖励
                                    $isOver = StockLevel::find()
                                        ->where(['is_delete' => 0,'level' => $userParent['level'], 'is_over' => 1, 'mall_id' => $this->order->mall_id, 'is_use' => 1])
                                        ->orderBy('level ASC')
                                        ->one();
                                    \Yii::warning("FillOrderPriceLogJob is_pay isOver=".var_export($isOver,true));
                                    if(!empty($isOver)){
                                        $price = floatval($level['over_price']);
                                    }
                                    break;
                                }
                            }
                        }
                        if ($price) {
                            $incomeLog = new FillIncomeLog();
                            $incomeLog->mall_id = $this->mall_id;
                            $incomeLog->user_id = $userParent['user_id'];
                            $incomeLog->price = floatval($price) * $item['num'];
                            $incomeLog->type = FillIncomeLog::OVER_INCOME;
                            $incomeLog->fill_price_log_id = 0;
                            $incomeLog->fill_order_detail_id = $item['id'];
                            if ($incomeLog->save()) {
                                $user = User::findOne($incomeLog->user_id);
                                \Yii::$app->currency->setUser($user)->income
                                    ->add(floatval($incomeLog->price), "代理商拿越级奖ID：{$incomeLog->id} 的佣金发放", 0, 1, 0, 1);
                            }
                        }
                    }
                    //平级层数大于0
                    if($equal_level > 0 && $item['equal_level_list']) {
                        //计算平级奖
                        $level = StockLevel::find()
                            ->where(['is_delete' => 0,'level' => $stockAgent->level, 'is_equal' => 1, 'mall_id' => $this->order->mall_id, 'is_use' => 1])
                            ->orderBy('level ASC')
                            ->one();
                        \Yii::warning("FillOrderPriceLogJob is_pay level=".var_export($level,true));
                        if (!empty($level)) {
                            $list = UserParent::find()
                                ->alias('up')
                                ->where(['up.user_id' => $this->order->user_id, 'up.is_delete' => 0])
                                ->leftJoin(['sa' => StockAgent::tableName()], 'sa.user_id=up.parent_id')
                                ->andWhere(['sa.level' => $level->level, 'sa.is_delete' => 0])
                                ->orderBy('up.level ASC')
                                ->select('sa.user_id,sa.level')
                                ->limit($equal_level)
                                ->asArray()
                                ->all();
                            if (!empty($list)) {
                                $equal_price_level_list = SerializeHelper::decode($item['equal_level_list']);
                                \Yii::warning("FillOrderPriceLogJob is_pay equal_price_level_list=".var_export($equal_price_level_list,true));
                                foreach ($list as $sa) {
                                    foreach ($equal_price_level_list as $equal) {
                                        if ($equal['level'] == $sa['level']) {
                                            $equal_price = $equal['equal_price'];
                                            if ($equal_price) {
                                                $incomeLog = new FillIncomeLog();
                                                $incomeLog->mall_id = $this->mall_id;
                                                $incomeLog->user_id = $sa['user_id'];
                                                $incomeLog->price = floatval($equal_price) * $item['num'];
                                                $incomeLog->type = FillIncomeLog::EQUAL_INCOME;
                                                $incomeLog->fill_price_log_id = 0;
                                                $incomeLog->fill_order_detail_id = $item['id'];
                                                if ($incomeLog->save()) {
                                                    $user = User::findOne($incomeLog->user_id);
                                                    \Yii::$app->currency->setUser($user)->income
                                                        ->add(floatval($incomeLog->price), "代理商拿货平级奖ID：{$incomeLog->id} 的佣金发放", 0, 1, 0, 1);
                                                    $stock_agent = StockAgent::findOne(['user_id' => $incomeLog->user_id, 'is_delete' => 0]);
                                                    if ($stock_agent) {
                                                        \Yii::warning('找到代理商');
                                                        $stock_agent->total_price += floatval($incomeLog->price);
                                                        if (!$stock_agent->save()) {
                                                            \Yii::warning(SerializeHelper::encode($stock_agent));
                                                        }
                                                    } else {
                                                        \Yii::warning('找不到代理商');
                                                    }

                                                }
                                            }
                                        }
                                    }
                                    unset($equal);
                                }
                            }
                        }
                    }
                }
            }

            //只能向比自己代理商等级高的上级补货，满足这个条件，才有FillPriceLog记录
            $log_list = FillPriceLog::find()
                ->alias('l')
                ->leftJoin(['g' => StockGoods::tableName()], 'g.goods_id=l.goods_id')
                ->where(['l.order_id' => $this->order->id, 'l.is_delete' => 0])
                ->andWhere(['!=', 'l.status', '-1'])
                ->select('l.*,g.equal_level_list,g.over_level_list')
                ->asArray()
                ->all();
            \Yii::warning("FillOrderPriceLogJob is_pay log_list=".var_export($log_list,true));
            if (count($log_list)) {
                \Yii::warning('有补货订单奖励记录');
                //计算货款收益
                foreach ($log_list as $log) {
                    $incomeLog = new FillIncomeLog();
                    $incomeLog->mall_id = $this->mall_id;
                    $incomeLog->user_id = $log['user_id'];
                    $incomeLog->price = floatval($log['price']);
                    $incomeLog->type = FillIncomeLog::LOAN_INCOME;
                    $incomeLog->fill_price_log_id = $log['id'];
                    $incomeLog->fill_order_detail_id = $log['fill_order_detail_id'];
                    if ($incomeLog->save()) {
                        $user = User::findOne($incomeLog->user_id);
                        \Yii::$app->currency->setUser($user)->income
                            ->add(floatval($incomeLog->price), "代理商拿货货款发放，ID：{$incomeLog->id}", 0, 1, 0, 1);
                        //货款收益
                        $fill_order = FillOrder::findOne(['is_delete' => 0, 'id' => $log['order_id']]);
                        if ($fill_order) {
                            $log1 = new GoodsPriceLog();
                            $log1->mall_id = $this->mall_id;
                            $log1->user_id = $incomeLog->user_id;
                            $log1->price = $incomeLog->price;
                            $log1->goods_id = $log['goods_id'];
                            $log1->log_id = $log['id'];
                            $log1->order_no = $fill_order->order_no;
                            $log1->buy_user_id = $fill_order->user_id;
                            $log1->type = 1;
                            $log1->save();
                        }
                    }
                }
                unset($log);
            }
        }
        FillPriceLog::updateAll(['is_price' => 1], ['order_id' => $this->order->id, 'status' => 1, 'is_price' => 0]);
    }
}