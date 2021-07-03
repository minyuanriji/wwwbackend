<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 分销佣金订单处理任务类
 * Author: zal
 * Date: 2020-05-25
 * Time: 17:05
 */

namespace app\plugins\boss\jobs;

use app\models\Mall;

use app\plugins\boss\models\BossSetting;
use app\plugins\boss\models\BossPriceJob;
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
        \Yii::warning('执行股东分红定时队列');
        $is_enable = BossSetting::getValueByKey(BossSetting::IS_ENABLE, $this->mall->id);
        if ($is_enable) {
            $period = BossSetting::getValueByKey(BossSetting::COMPUTE_PERIOD, $this->mall->id);
            if ($period == 0) {//天
                $date = date("Y-m-d", strtotime("+1 day"));
                $date_time = strtotime(date("Y-m-d 03:30:00", strtotime("+1 day")));
            }
            if ($period == 1) { //周
                $date = date("Y-m-d", strtotime("next Monday"));
                $date_time = strtotime(date("Y-m-d 03:30:00", strtotime("next Monday")));
            }
            if ($period == 2) { //月
                $date = date("Y-m-01", strtotime("+1 month"));
                $date_time = strtotime(date("Y-m-01 03:30:00", strtotime("+1 month")));
            }
            $job = BossPriceJob::findOne(['date' => $date, 'mall_id' => $this->mall->id]);
            if (!$job) {
                $job = new BossPriceJob();
                $job->mall_id = $this->mall->id;
                $job->date = $date;
                $job->queue_id = 0;
                if ($job->save()) {
                    $id = \Yii::$app->queue->delay($date_time - time())->push(new BossPriceLogJob(['mall' => $this->mall]));
                    $job->queue_id = $id;
                    $job->save();
                }
            }
        }
    }
}