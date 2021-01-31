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

use app\models\CommonOrderDetail;
use app\models\Mall;
use app\models\PriceLog;
use app\models\User;
use app\models\UserChildren;
use app\plugins\distribution\models\Distribution;
use app\plugins\distribution\models\DistributionSetting;
use app\plugins\distribution\models\RebuyLevel;
use app\plugins\distribution\models\RebuyLog;
use app\plugins\distribution\models\RebuyPriceJob;
use app\plugins\distribution\models\RebuyPriceLog;
use app\plugins\distribution\models\SubsidyPriceJob;
use function GuzzleHttp\Psr7\str;
use yii\base\Component;
use yii\queue\JobInterface;
use yii\queue\Queue;

class CheckTimerJob extends Component implements JobInterface
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
        \Yii::warning('执行定时队列');
        $is_rebuy = DistributionSetting::getValueByKey('is_rebuy', $this->mall->id);
        if ($is_rebuy) {
            $date = DistributionSetting::getValueByKey('rebuy_price_date', $this->mall->id);
            $next_month = date("Y-m", strtotime(" +1 month"));
            $job = RebuyPriceJob::findOne(['month' => $next_month, 'mall_id' => $this->mall->id]);
            if (!$job) {
                $job = new RebuyPriceJob();
                $job->mall_id = $this->mall->id;
                $job->month = $next_month;
                $job->queue_id = 0;
                if ($job->save()) {
                    $next_month = date("Y-m", strtotime(" +1 month"));
                    $next_month = $next_month . '-' . $date . ' 04:0:1';
                    $next_time = intval(strtotime($next_month));
                    $id = \Yii::$app->queue->delay($next_time - time())->push(new RebuyPriceLogJob(['mall' => $this->mall]));
                    $job->queue_id = $id;
                    $job->save();
                }
            }
        }
        $is_subsidy = DistributionSetting::getValueByKey(DistributionSetting::IS_SUBSIDY, $this->mall->id);
        if ($is_subsidy) {
            $date = DistributionSetting::getValueByKey(DistributionSetting::SUBSIDY_PRICE_DATE, $this->mall->id);
            $next_month = date("Y-m", strtotime(" +1 month"));
            $job = SubsidyPriceJob::findOne(['month' => $next_month, 'mall_id' => $this->mall->id]);
            if (!$job) {
                $job = new SubsidyPriceJob();
                $job->mall_id = $this->mall->id;
                $job->month = $next_month;
                $job->queue_id = 1;
                if ($job->save()) {
                    $next_month = date("Y-m", strtotime(" +1 month"));
                    $next_month = $next_month . '-' . $date . ' 04:0:1';
                    $next_time = intval(strtotime($next_month));
                    \Yii::$app->queue->delay($next_time - time())->push(new SubsidyPriceLogJob(['mall' => $this->mall]));
                }
            }
        }
    }
}