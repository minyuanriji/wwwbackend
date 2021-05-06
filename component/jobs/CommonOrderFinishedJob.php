<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * Created by PhpStorm
 * Author: ganxiaohao
 * Date: 2020-05-22
 * Time: 10:01
 */

namespace app\component\jobs;


use app\models\CommonOrder;
use app\models\UserGrowth;
use yii\base\Component;
use yii\db\Exception;
use yii\queue\JobInterface;
use yii\queue\Queue;

class CommonOrderFinishedJob extends Component implements JobInterface
{

    public $common_order_id;
    public $status;
    public $user_id;
    public $mall_id;


    /**
     * @param Queue $queue which pushed and is handling the job
     * @return void|mixed result of the job execution
     */
    public function execute($queue)
    {
        \Yii::warning("---CommonOrderFinishedJob start---");
        // TODO: Implement execute() method.
        try{
            $query = CommonOrder::find()->where(['user_id' => $this->user_id, 'status' => CommonOrder::STATUS_IS_COMPLETE]);
            $total_price = $query->sum('pay_price');
            \Yii::warning("CommonOrderFinishedJob total_price=".$total_price);
            $growth = UserGrowth::findOne(['user_id' => $this->user_id, 'keyword' => UserGrowth::KEY_SELF_BUY_ORDER_PRICE, 'is_delete' => 0]);
            \Yii::warning("---CommonOrderFinishedJob growth=".var_export($growth,true));
            if ($total_price) {
                if (!$growth) {
                    $growth = new UserGrowth();
                    $growth->user_id = $this->user_id;
                    $growth->mall_id = $this->mall_id;
                    $growth->keyword = UserGrowth::KEY_SELF_BUY_ORDER_PRICE;
                }
                $growth->value = $total_price;
                $growth->save();
            }
            $total_count = $query->count();
            \Yii::warning("CommonOrderFinishedJob total_count=".$total_count);
            if ($total_count) {
                $growth = UserGrowth::findOne(['user_id' => $this->user_id, 'keyword' => UserGrowth::KEY_SELF_BUY_ORDER_COUNT, 'is_delete' => 0]);
                if (!$growth) {

                    $growth = new UserGrowth();
                    $growth->user_id = $this->user_id;
                    $growth->mall_id = $this->mall_id;
                    $growth->keyword = UserGrowth::KEY_SELF_BUY_ORDER_COUNT;
                }
                $growth->value = $total_count;
                $growth->save();
            }
            \Yii::warning("---CommonOrderFinishedJob end---");
        }catch (Exception $ex){
            \Yii::error("---CommonOrderFinishedJob error File:".$ex->getFile().";Line:".$ex->getLine().";message:".$ex->getMessage());
        }
    }
}