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


use app\models\Mall;
use app\models\User;
use app\models\UserChildren;
use app\plugins\distribution\models\Distribution;
use app\plugins\distribution\models\DistributionSetting;

use app\plugins\distribution\models\SubsidyPriceLog;
use app\plugins\distribution\models\SubsidySetting;


use yii\base\Component;
use yii\queue\JobInterface;
use yii\queue\Queue;

class SubsidyPriceLogJob extends Component implements JobInterface
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
        \Yii::warning('补贴奖励开始执行');

        $distribution_level = DistributionSetting::getValueByKey(DistributionSetting::LEVEL, $this->mall->id);
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

            //计算团队新增
            $team_count = UserChildren::find()
                ->alias('uc')
                ->where(['uc.user_id' => $distribution->user_id, 'uc.is_delete' => 0])
                ->andWhere(['uc.level' => $distribution_level])
                ->leftJoin(['u' => User::tableName()], 'u.id=uc.child_id')
                ->andWhere(['>', 'u.created_at', $last_month])
                ->andWhere(['<', 'u.created_at', $cur_month])
                ->count();
            $subsidy = null;
            $list = SubsidySetting::find()->where(['is_delete' => 0, 'mall_id' => $this->mall->id, 'distribution_level' => $level, 'is_enable' => 1])->orderBy('min_num DESC')->all();
            foreach ($list as $item) {
                /**
                 * @var SubsidySetting $item
                 */


                if ($team_count >= $item->min_num) {
                    $subsidy = $item;
                    break;
                }
            }
            unset($item);
            if ($subsidy) {
                $total_price = intval($team_count) * $subsidy->price;
                if ($total_price) {
                    $log = new SubsidyPriceLog();
                    $log->team_new_count = $team_count;
                    $log->user_id = $distribution->user_id;
                    $log->price = $total_price;
                    $log->mall_id = $distribution->mall_id;
                    $log->month = date('Y-m', strtotime('last month'));
                    if (!$log->save()) {
                        \Yii::warning('保存补贴记录失败' . json_encode($log->getErrors()));
                    }
                    $user = User::findOne($log->user_id);
                    \Yii::$app->currency->setUser($user)->income
                        ->add(floatval($log->price), "补贴奖励", 0, 0);
                }
                unset($log);
            }
        }
        unset($distribution);
    }
}