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

class GroupBuyGoodsEndJob extends Component implements JobInterface
{
    public $id;

    /**
     * @param Queue $queue which pushed and is handling the job
     * @return void|mixed result of the job execution
     */
    public function execute($queue)
    {
        \Yii::warning("------拼团商品活动自动结束 start---------");
        $model = PluginGroupBuyGoods::findOne($this->id);
        $model->status = 2;
        $model->save();
        \Yii::warning("------拼团商品活动自动结束 end---------");
        return true;
    }
}