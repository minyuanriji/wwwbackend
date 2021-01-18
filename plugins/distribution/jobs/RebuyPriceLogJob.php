<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 分销佣金订单处理任务类
 * Author: zal
 * Date: 2020-05-25
 * Time: 17:05
 */

namespace app\plugins\distribution\jobs;

use app\helpers\SerializeHelper;
use app\models\CommonOrderDetail;
use app\models\Mall;
use app\models\PriceLog;
use app\models\User;
use app\models\UserChildren;
use app\models\UserParent;
use app\plugins\distribution\models\Distribution;
use app\plugins\distribution\models\DistributionGoods;
use app\plugins\distribution\models\DistributionGoodsDetail;
use app\plugins\distribution\models\DistributionLevel;
use app\plugins\distribution\models\DistributionSetting;
use app\plugins\distribution\models\RebuyLevel;
use app\plugins\distribution\models\RebuyLog;
use app\plugins\distribution\models\RebuyPriceLog;
use app\plugins\distribution\models\Team;
use app\plugins\distribution\models\TeamPriceLog;
use app\plugins\distribution\Plugin;
use yii\base\Component;
use yii\base\Exception;
use yii\queue\JobInterface;
use yii\queue\Queue;

class RebuyPriceLogJob extends Component implements JobInterface
{
    /**
     * @var Mall $mall ;
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
        \Yii::warning('复购分佣队列');
        \Yii::$app->setMall($this->mall);

        $distribution_level = DistributionSetting::getValueByKey(DistributionSetting::LEVEL, $this->mall->id);
        $is_team = DistributionSetting::getValueByKey(DistributionSetting::IS_TEAM, $this->mall->id);

        if (!$distribution_level) {
            return;
        }
        $distribution_level = intval($distribution_level);
        $distribution_list = Distribution::find()->where(['mall_id' => $this->mall->id, 'is_delete' => 0])->all();
        $last_month = date('Y-m-01 0:0:1', strtotime('last month'));
        $last_month = strtotime($last_month);
        $cur_month = strtotime(date("Y-m-01", time()));
        foreach ($distribution_list as $distribution) {
            /**
             * @var Distribution $distribution ;
             */
            $level = $distribution->level;
            $count = UserChildren::find()
                ->alias('uc')
                ->where(['uc.user_id' => $distribution->user_id, 'uc.is_delete' => 0])
                ->andWhere(['uc.level' => 1])
                ->leftJoin(['u' => User::tableName()], 'u.id=uc.child_id')
                ->andWhere(['>', 'u.created_at', $last_month])
                ->andWhere(['<', 'u.created_at', $cur_month])
                ->count();
            //计算团队新增
            $team_count = UserChildren::find()
                ->alias('uc')
                ->where(['uc.user_id' => $distribution->user_id, 'uc.is_delete' => 0])
                ->andWhere(['uc.level' => $distribution_level])
                ->leftJoin(['u' => User::tableName()], 'u.id=uc.child_id')
                ->andWhere(['>', 'u.created_at', $last_month])
                ->andWhere(['<', 'u.created_at', $cur_month])
                ->count();
            $rebuy_level = null;
            $level_list = RebuyLevel::find()->where(['is_delete' => 0, 'mall_id' => $this->mall->id, 'distribution_level' => $level, 'is_enable' => 1])->orderBy('level DESC')->all();

            foreach ($level_list as $item) {
                /**
                 * @var RebuyLevel $item
                 */
                if ($item->upgrade_type == 1) {
                    if ($count >= $item->child_num) {
                        $rebuy_level = $item;
                        break;
                    } else {
                        if ($item->team_child_num != -1) {
                            if ($team_count >= $item->team_child_num) {
                                $rebuy_level = $item;
                                break;
                            }
                        }
                    }
                }
                if ($item->upgrade_type == 0) {
                    if ($item->team_child_num != -1) {
                        if ($team_count < $item->team_child_num) {
                            $rebuy_level = $item;
                            break;
                        }
                    }
                    if ($count < $item->child_num) {
                        $rebuy_level = $item;
                        break;
                    }
                }
            }
            unset($item);
            if ($rebuy_level) {
                $total_price = 0;
                $total_order_price = 0;
                $query = RebuyLog::find()->where(['user_id' => $distribution->user_id, 'is_price' => 0, 'goods_type' => CommonOrderDetail::TYPE_MALL_GOODS])
                    ->andWhere(['<', 'created_at', $cur_month])
                    ->andWhere(['status' => 1]);
                $query1 = clone $query;
                $sum_price = $query1->sum('price');
                $sum_num = $query1->sum('num');
                $total_order_price = $sum_price;
                if ($rebuy_level->price_type == 1) {
                    //固定金额
                    //上个月的日志
                    if ($sum_num) {
                        $total_price = intval($sum_num) * $rebuy_level->price;
                    }
                } else {
                    if ($sum_price) {
                        $total_price = floatval($sum_price) * $rebuy_level->price / 100;
                    }
                }
                if ($total_price) {
                    $log = new RebuyPriceLog();
                    $log->total_order_goods_num = $sum_num;
                    $log->user_id = $distribution->user_id;
                    $log->price = $total_price;
                    $log->total_order_price = $total_order_price;
                    $log->mall_id = $distribution->mall_id;
                    $log->month = date('Y-m', strtotime('last month'));
                    if (!$log->save()) {
                        \Yii::warning('保存复购奖励佣金记录失败' . json_encode($log->getErrors()));
                    }
                    $user = User::findOne($log->user_id);
                    \Yii::$app->currency->setUser($user)->income
                        ->add(floatval($log->price), "复购佣金奖励", 0, 0);
                    if ($is_team) {
                        $distribution2 = Distribution::findOne(['user_id' => $user->parent_id, 'is_delete' => 0]);
                        if ($distribution2) {
                            $team = Team::findOne(['mall_id' => $distribution2->mall_id, 'parent_level' => $distribution2->level, 'child_level' => $distribution->level]);
                            if ($team) {
                                $price_type = $team->price_type;//0 百分比   1 固定
                                $team_price_log = new TeamPriceLog();
                                $team_price_log->user_id = $distribution2->user_id;
                                $team_price_log->mall_id = $distribution2->mall_id;
                                if ($price_type == 1) {
                                    $price = $team->price;
                                } else {
                                    $price = $team->price * $log->price / 100;
                                }
                                $team_price_log->price = $price;
                                if ($team_price_log->save()) {
                                    $user = User::findOne($team_price_log->user_id);
                                    \Yii::$app->currency->setUser($user)->income
                                        ->add(floatval($team_price_log->price), "复购团队奖励", 0, 0);
                                }
                            }
                        }
                    }
                }
                unset($log);
            }
        }
        unset($distribution);
        RebuyLog::updateAll(['is_price' => 1], "mall_id={$this->mall->id} and created_at < {$cur_month} and created_at >= {$last_month} and status=1");
    }
}