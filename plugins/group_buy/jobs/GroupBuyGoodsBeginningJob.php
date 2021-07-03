<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 名片客户任务类
 * Author: zal
 * Date: 2020-07-23
 * Time: 18:35
 */

namespace app\plugins\group_buy\jobs;

use app\plugins\group_buy\models\PluginGroupBuyGoods;
use yii\base\Component;
use yii\queue\JobInterface;
use yii\queue\Queue;

class GroupBuyGoodsBeginningJob extends Component implements JobInterface
{
    public $id;

    /**
     * @param Queue $queue which pushed and is handling the job
     * @return void|mixed result of the job execution
     */
    public function execute($queue)
    {
        \Yii::info("------拼团商品活动自动开启 start---------");
        $model = PluginGroupBuyGoods::findOne($this->id);
        if ($model->status == 0) {
            $model->status = 1;
            $model->save();
        }
        \Yii::info("------拼团商品活动自动开启 end---------");
        return true;
    }
}