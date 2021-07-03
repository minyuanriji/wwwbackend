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


use app\models\CommonOrder;
use app\models\CommonOrderDetail;
use app\plugins\stock\models\StockSetting;
use app\plugins\stock\models\UpgradeBag;
use yii\base\BaseObject;
use yii\queue\JobInterface;
use yii\queue\Queue;

class CommonOrderPayedJob extends BaseObject implements JobInterface
{
    public $order_id;
    public $order_type;

    /**
     * @param Queue $queue which pushed and is handling the job
     * @return void|mixed result of the job execution
     */
    public function execute($queue)
    {
        // TODO: Implement execute() method.
        \Yii::warning('---------- CommonOrderPayedJob execute start -----------------');
        $common_order_detail_list = CommonOrderDetail::find()->where(['order_id' =>$this->order_id, 'is_delete' => 0])->all();
        foreach ($common_order_detail_list as $order) {
            /**
             * @var  CommonOrderDetail $order
             */
            \Yii::$app->queue->delay(6)->push(new StockLogJob([
                'order' => $order,
                'common_order_detail_id' => $order->id,
                'type' => 3
            ]));
        }
        unset($order);
    }
}