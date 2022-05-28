<?php

namespace app\plugins\perform_distribution\jobs;

use app\forms\common\UserIncomeModifyForm;
use app\models\OrderDetail;
use app\models\User;
use app\plugins\perform_distribution\models\AwardOrder;
use app\plugins\perform_distribution\models\PerformDistributionGoods;
use yii\base\Component;
use yii\queue\JobInterface;
use yii\queue\Queue;

class AwardOrderCreateJob extends Component implements JobInterface{

    public $order;

    /**
     * @param Queue $queue which pushed and is handling the job
     * @return void|mixed result of the job execution
     */
    public function execute($queue){
        if(!$this->checkOrder())
            return;
        $t = \Yii::$app->getDb()->beginTransaction();
        try {
            $details = $this->order->detail;
            foreach($details as $detail){
                if(!$this->checkOrderDetail($detail))
                    continue;

                //获取业绩奖励商品
                $awardGoods = PerformDistributionGoods::findOne([
                    "mall_id"   => $this->order->mall_id,
                    "goods_id"  => $detail->goods_id,
                    "is_delete" => 0
                ]);
                if(!$awardGoods)
                    continue;

                //获取到业绩奖励信息
                $awardInfo = $awardGoods->getAwardInfo($this->order, $detail);
                foreach($awardInfo['award_users'] as $key => $awardUser){
                    $awardOrder = AwardOrder::findOne([
                        "mall_id"         => $this->order->mall_id,
                        "user_id"         => $awardUser['user_id'],
                        "order_id"        => $this->order->id,
                        "order_detail_id" => $detail->id
                    ]);

                    //不要重复奖励咯
                    if($awardOrder)
                        continue;

                    //生成待结算业绩奖励订单
                    $awardOrder = new AwardOrder([
                        "mall_id"         => $this->order->mall_id,
                        "user_id"         => $awardUser['user_id'],
                        "order_id"        => $this->order->id,
                        "order_detail_id" => $detail->id,
                        "created_at"      => time()
                    ]);
                    $awardOrder->updated_at = time();
                    $awardOrder->price      = $awardUser['price'];
                    $awardOrder->status     = 0;
                    $awardOrder->award_info = json_encode($awardInfo, JSON_UNESCAPED_UNICODE);
                    if(!$awardOrder->save()){
                        throw new \Exception(json_encode($awardOrder->getErrors()));
                    }

                    //生成待结算收益记录
                    $incomeModifyForm = new UserIncomeModifyForm([
                        "price"       => $awardUser['price'],
                        "type"        => 1,
                        "flag"        => 0,
                        "source_id"   => $awardOrder->id,
                        "source_type" => "perform_distribution_award_order",
                        "desc"        => "业绩分配奖励收益",
                        "is_manual"   => 0
                    ]);
                    $incomeModifyForm->modify(User::findOne($awardUser['user_id']), false);
                }
            }

            $t->commit();
        }catch (\Exception $e){
            $t->rollBack();
            \Yii::error($e->getMessage());
        }

    }

    /**
     * 检查订单详情
     * @return boolean
     */
    private function checkOrderDetail(OrderDetail $orderDetail){
        return $orderDetail->is_refund || $orderDetail->is_delete ? false : true;
    }

    /**
     * 检查订单
     * @return boolean
     */
    private function checkOrder(){
        $order = $this->order;
        if($order && $order->is_pay && !$order->is_delete && !$order->is_recycle && $order->cancel_status == 0){
            return true;
        }else{
            return false;
        }
    }
}