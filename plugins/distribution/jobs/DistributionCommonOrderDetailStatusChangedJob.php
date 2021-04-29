<?php
namespace app\plugins\distribution\jobs;

use app\helpers\SerializeHelper;
use app\models\CommonOrderDetail;
use app\models\Mall;
use app\models\Order;
use app\models\PriceLog;
use app\models\User;
use app\plugins\distribution\models\Distribution;
use app\plugins\distribution\Plugin;
use yii\base\Component;
use yii\base\Exception;
use yii\queue\JobInterface;

class DistributionCommonOrderDetailStatusChangedJob extends Component implements JobInterface{

    public $common_order_detail_id;

    public $order;

    public function execute($queue){

        \Yii::warning('-------------------------------------------------------------------------------------------------------');
        \Yii::warning('分销记录队列开始执行');

        $commonOrderDetail = CommonOrderDetail::findOne($this->common_order_detail_id);
        \Yii::warning('分销订单数据'.var_export($commonOrderDetail,true));
        if (!$commonOrderDetail) {
            \Yii::warning("---公共订单不存在：{$this->common_order_detail_id}---");
            return;
        }

        $order = Order::findOne($commonOrderDetail->order_id);
        \Yii::warning('主订单数据'.var_export($order,true));
        if (!$order) {
            \Yii::warning("---主订单不存在：{$commonOrderDetail->order_id}  商城ID{$commonOrderDetail->mall_id}---");
            return;
        }

        $this->order = $order;

        $plugin = new Plugin();
        $sign = $plugin->getName();

        $mall = Mall::findOne($this->order->mall_id);
        if (!$mall) {
            \Yii::warning("---处理分销队列时候商城不存在公共订单ID：{$this->common_order_detail_id} 商城ID{$this->order->mall_id}---");
            return;
        }

        \Yii::$app->setMall($mall);
        \Yii::warning("---分销佣金订单记录处理开始---");
        //这里需要从common_order_detail 里面获取商品的类型

        $user = User::findOne($order->user_id);
        if (!$user) {
            \Yii::warning('分销订单找不到用户');
            return;
        }

        //拿出所有分佣记录
        $logList = PriceLog::find()->andWhere([
            'order_id'  => $this->order->id,
            'is_delete' => 0,
            'is_price'  => 0,
            'sign'      => $sign
        ])->andWhere("status <> '-1'")->all();

        $logList = $logList ? $logList : [];

        foreach($logList as $log){

            $user = User::findOne($log->user_id);
            $distribution = Distribution::findOne(['user_id' => $log->user_id, 'is_delete' => 0]);

            //分佣记录未处理，订单状态为待发货、待收货、待评价、售后申请中、售后完成的
            //1.设置分佣记录为有效但未发放
            //2.增加未结算佣金
            if(0 == $log->status && in_array($order->status, [Order::STATUS_WAIT_DELIVER,
                Order::STATUS_WAIT_RECEIVE, Order::STATUS_WAIT_COMMENT,
                Order::STATUS_SALES_APPLY, Order::STATUS_SALES_COMPLETE])){
                \Yii::warning('订单符合分润');
                $log->status = 1;

                if($user){
                    $user->total_income += floatval($log->price);
                    $user->income_frozen += floatval($log->price);
                }
                if($distribution){
                    $distribution->total_price += floatval($log->price);
                    $distribution->frozen_price += floatval($log->price);
                }

            }

            //订单状态为 已完成 的
            //开始佣金到账
            if(in_array($order->status, [Order::STATUS_COMPLETE])){
                $log->is_price = 1;
                $log->status = 1;
            }

            //佣记录已处理，订单状态为 取消待处理、已关闭的
            //待结算佣金扣减
            if(1 == $log->status && in_array($order->status, [Order::STATUS_CANCEL_WAIT, Order::STATUS_CLOSE])){
                if($user){
                    $user->total_income -= floatval($log->price);
                    $user->income_frozen -= floatval($log->price);
                }
                if($distribution){
                    $distribution->total_price -= floatval($log->price);
                    $distribution->frozen_price -= floatval($log->price);
                }
                $log->status = -1;
            }

            if(1 == $log->status && 1 == $log->is_price && $log->price > 0){ //佣金 已处理、开始发放
                $user->income_frozen -= floatval($log->price);
                $user->income += floatval($log->price);
                \Yii::$app->currency->setUser($user)->income
                    ->add(floatval($log->price), "分佣记录ID：{$log->id} 的佣金发放", $this->common_order_detail_id, 1);
            }

            if (!$log->save()) {
                \Yii::warning('佣金记录发放保存失败：' . SerializeHelper::encode($log->getErrors()));
                continue;
            }

            if(!$distribution || !$distribution->save()){
                \Yii::warning("分销商信息保存失败");
            }

            if(!$user || !$user->save()){
                \Yii::warning("用户信息保存失败");
            }

        }

    }
}