<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 经销佣金订单处理任务类
 * Author: zal
 * Date: 2020-05-25
 * Time: 17:05
 */

namespace app\plugins\boss\jobs;

use app\models\CommonOrderDetail;
use app\models\Mall;
use app\models\User;
use app\plugins\boss\models\Boss;
use app\plugins\boss\models\BossLevel;
use app\plugins\boss\models\BossPriceLog;
use app\plugins\boss\models\BossSetting;
use yii\base\Component;
use yii\queue\JobInterface;
use yii\queue\Queue;

class BossPriceLogJob extends Component implements JobInterface
{
    /**
     * @var Mall $mall
     */
    public $mall;
    /**
     *
     * @param Queue $queue
     * @return mixed|void
     * @throws \Exception
     */

    //TODO 还需要加入其他筛选添加 例如是商城商品还是其他商品
    public function execute($queue)
    {
        \Yii::$app->setMall($this->mall);
        $level_list = BossLevel::find()->where(['mall_id' => $this->mall->id, 'is_delete' => 0, 'is_enable' => 1])->all();
        $compute_type = BossSetting::getValueByKey(BossSetting::COMPUTE_TYPE, $this->mall->id);
        $period = BossSetting::getValueByKey(BossSetting::COMPUTE_PERIOD, $this->mall->id);
        if ($period == 0) {//天
            $start_time = strtotime(date("Y-m-d 00:00:00", strtotime("-1 day")));
            $end_time = $start_time + 1 * 24 * 60 * 60;
        }
        if ($period == 1) { //周
            $start_time = strtotime(date("Y-m-d 00:00:00", strtotime("last Monday")));
            $end_time = $start_time + 7 * 24 * 60 * 60;
        }
        if ($period == 2) { //月
            $start_time = strtotime(date("Y-m-01 00:00:00", strtotime("-1 month")));
            $end_time = strtotime(date("Y-m-01 00:00:00", time()));
        }
        $total_price = 0;
        if ($compute_type == 0) {
            $total_price = CommonOrderDetail::find()->where(['status' => 1, 'mall_id' => $this->mall->id, 'is_delete' => 0])
                ->andWhere(['>=', 'updated_at', $start_time])
                ->andWhere(['<', 'updated_at', $end_time])
                ->sum('price');
        }

        if ($compute_type == 0) {
            $total_price = CommonOrderDetail::find()->where(['status' => 1, 'mall_id' => $this->mall->id, 'is_delete' => 0])
                ->andWhere(['>=', 'updated_at', $start_time])
                ->andWhere(['<', 'updated_at', $end_time])
                ->sum('profit');
        }
        if (!$total_price || $total_price < 0) {
            \Yii::warning('分红池没有钱');
            return;
        }
        foreach ($level_list as $level) {  //找到股东等级
            /**
             * @var BossLevel $level
             */
            $boss_list = Boss::find()->where(['mall_id' => $this->mall->id, 'is_delete' => 0, 'level' => $level->level])->all();
            $boss_count = count($boss_list);
            if ($boss_count && $boss_count > 0) {
                $price = floatval($level->price) / 100 * floatval($total_price) / floatval($boss_count);
                $extra_price = floatval($level->extra_price) / 100 * floatval($total_price) / floatval($boss_count);
                if ($price > 0) {
                    foreach ($boss_list as $boss) {
                        /**
                         * @var Boss $boss
                         */
                        $log = new BossPriceLog();
                        $log->start_time = $start_time;
                        $log->end_time = $end_time;
                        $log->user_id = $boss->user_id;
                        $log->price = $price;
                        $log->mall_id = $boss->mall_id;
                        $log->type = 0;
                        if (!$log->save()) {
                            \Yii::warning('保存股东分红佣金记录失败' . json_encode($log->getErrors()));
                        }
                        $user = User::findOne($log->user_id);
                        \Yii::$app->currency->setUser($user)->income
                            ->add(floatval($log->price), "股东分红奖励", 0, 0);
                        $boss->total_price += $log->price;
                        //开始判断是否存在额外将
                        if ($level->is_extra) {
                            if ($level->extra_is_limit) {//存在上限
                                if ($boss->extra_price < $level->extra_limit_price) {
                                    $sub_price = floatval($level->extra_limit_price) - floatval($boss->extra_price);
                                    if ($sub_price > 0) {
                                        if (floatval($boss->extra_price) + floatval($extra_price) > floatval($level->extra_limit_price)) {
                                            $price = $sub_price;
                                        } else {
                                            $price = $extra_price;
                                        }
                                    } else {
                                        $price = 0;
                                    }
                                }
                            } else {
                                $price = $extra_price;
                            }
                            if ($price && $price > 0) {
                                $log = new BossPriceLog();
                                $log->start_time = $start_time;
                                $log->end_time = $end_time;
                                $log->user_id = $boss->user_id;
                                $log->price = $price;
                                $log->mall_id = $boss->mall_id;
                                $log->type = 1;
                                if (!$log->save()) {
                                    \Yii::warning('保存股东分红佣金记录失败' . json_encode($log->getErrors()));
                                }
                                \Yii::$app->currency->setUser($user)->income
                                    ->add(floatval($log->price), "股东分红额外奖励", 0, 0);
                                $boss->total_price+=$log->price;
                                $boss->extra_price+=$log->price;
                            }
                        }
                        $boss->save();
                    }
                    unset($boss);
                }
            }
        }
        unset($level);
    }
}