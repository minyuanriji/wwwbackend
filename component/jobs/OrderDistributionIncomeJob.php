<?php
namespace app\component\jobs;


use app\models\CommonOrderDetail;
use app\models\IncomeLog;
use app\models\Mall;
use app\models\Order;
use app\models\OrderDetail;
use app\models\PriceLog;
use app\models\User;
use app\plugins\distribution\models\Distribution;
use app\plugins\distribution\models\DistributionGoods;
use app\plugins\distribution\models\DistributionGoodsDetail;
use app\plugins\distribution\models\DistributionLevel;
use app\plugins\distribution\models\DistributionSetting;
use yii\base\Component;
use yii\queue\JobInterface;

class OrderDistributionIncomeJob extends Component implements JobInterface{

    public $common_order_detail_id;

    private $is_debug = false;


    public function execute($queue){
        $t = \Yii::$app->getDb()->beginTransaction();
        try {
            $commonOrderDetail = CommonOrderDetail::find()->orderBy("process_priority_level ASC")->where([
                "is_delete"       => 0,
                "is_distribution" => 0
            ])->limit(1)->one();
            if(!$commonOrderDetail){
                throw new \Exception("暂无需要分佣记录");
            }

            $isDistribution = $commonOrderDetail->is_distribution;

            $this->common_order_detail_id = $commonOrderDetail->id;

            //获取订单
            $order = $commonOrderDetail->order;
            if(!$order || $order->is_delete){
                throw new \Exception("无法获取订单");
            }

            //获取订单详情
            $orderDetail = $commonOrderDetail->orderDetail;
            if(!$orderDetail){
                throw new \Exception("无法获取订单详情");
            }

            $mall = Mall::findOne($commonOrderDetail->mall_id);
            if (!$mall) {
                throw new \Exception("商城不存在");
            }

            \Yii::$app->setMall($mall);

            $user = User::findOne($commonOrderDetail->user_id);
            if (!$user) {
                throw new \Exception("找不到订单用户");
            }

            //默认的分佣设置
            $level          = DistributionSetting::getValueByKey(DistributionSetting::LEVEL);
            $first_price1   = DistributionSetting::getValueByKey(DistributionSetting::FIRST_PRICE);
            $second_price1  = DistributionSetting::getValueByKey(DistributionSetting::SECOND_PRICE);
            $third_price1   = DistributionSetting::getValueByKey(DistributionSetting::THIRD_PRICE);
            $price_type     = DistributionSetting::getValueByKey(DistributionSetting::PRICE_TYPE);
            $is_self_buy    = DistributionSetting::getValueByKey(DistributionSetting::IS_SELF_BUY);
            if (empty($level) || $level < 1) {
                throw new \Exception("未开启分销");
            }

            $is_alone = 0;
            $distribution_detail_list = [];
            $goods_type = $commonOrderDetail->goods_type;
            if ($goods_type == CommonOrderDetail::TYPE_MALL_GOODS) {
                //商城商品
                $distribution_goods = DistributionGoods::findOne(['goods_id' => $commonOrderDetail->goods_id, 'is_delete' => 0, 'is_alone' => 1]);  //这里要加入
                if ($distribution_goods) { //独立设置
                    $is_alone = 1;
                    $distribution_detail_list = DistributionGoodsDetail::find()->andWhere([
                        'distribution_goods_id' => $distribution_goods->id,
                        'is_delete'             => 0
                    ])->all();
                }
            }

            $distribution_list = []; //先找出分销商
            if ($is_self_buy) { //分销内购
                $distribution1 = Distribution::findOne(['user_id' => $commonOrderDetail->user_id, 'is_delete' => 0]);
                if ($distribution1) {
                    $distribution_list[0] = $distribution1;
                } else {
                    throw new \Exception("无法获取分销用户");
                }

                $distribution2 = Distribution::findOne(['user_id' => $user->parent_id, 'is_delete' => 0]);//二级
                if ($distribution2) {
                    $distribution_list[1] = $distribution2;
                    $parent2 = User::findOne($distribution2->user_id);
                    if ($parent2) {
                        $distribution3 = Distribution::findOne(['user_id' => $parent2->parent_id, 'is_delete' => 0]);//三级
                        if ($distribution3) {
                            $distribution_list[2] = $distribution3;
                        }
                    }
                }
            }else{
                $distribution1 = Distribution::findOne(['user_id' => $user->parent_id, 'is_delete' => 0]);//一级
                if ($distribution1) {
                    $distribution_list[0] = $distribution1;
                    $parent1 = User::findOne($distribution1->user_id);
                    if ($parent1) {
                        $distribution2 = Distribution::findOne(['user_id' => $parent1->parent_id, 'is_delete' => 0]);//二级
                        if ($distribution2) {
                            $distribution_list[1] = $distribution2;
                            $parent2 = User::findOne($distribution2->user_id);
                            if ($parent2) {
                                $distribution3 = Distribution::findOne(['user_id' => $parent2->parent_id, 'is_delete' => 0]);//三级
                                if ($distribution3) {
                                    $distribution_list[2] = $distribution3;
                                }
                            }
                        }
                    }
                }
            }

            for ($i = 0; $i < $level; $i++) {
                $is_level = 0;
                $user_level = $i + 1; //用户层级

                if(count($distribution_list) <= $i)  break;

                $distribution = $distribution_list[$i];

                if(!$distribution) continue;

                $sign = "distribution";
                $log = PriceLog::findOne([
                    'common_order_detail_id' => $this->common_order_detail_id,
                    'is_delete'              => 0,
                    'user_id'                => $distribution->user_id,
                    'sign'                   => $sign,
                    'level'                  => $user_level
                ]);

                $price = 0;
                if (!$log) {
                    $this->debugOutput("生成分佣记录");
                    $log = new PriceLog();
                    $log->mall_id                = \Yii::$app->mall->id;
                    $log->user_id                = $distribution->user_id;
                    $log->status                 = 0;
                    $log->common_order_detail_id = $this->common_order_detail_id;
                    $log->child_id               = $commonOrderDetail->user_id;
                    $log->level                  = $user_level;
                    $log->order_id               = $commonOrderDetail->order_id;
                    $log->sign                   = $sign;
                }

                if ($is_alone) { //独立分销机制
                    $this->debugOutput("独立分销机制");
                    $first_price = $second_price = $third_price = 0;
                    $price_type = $distribution_goods->share_type == 0 ? 2 : 1;
                    if ($distribution_goods->attr_setting_type == 1) { //按照规格
                        $this->debugOutput("按照规格");
                        foreach ($distribution_detail_list as $detail) {
                            if ($detail->level == $distribution->level && $detail->goods_attr_id == $commonOrderDetail->attr_id) {
                                $first_price2  = $detail->commission_first;
                                $second_price2 = $detail->commission_second;
                                $third_price2  = $detail->commission_third;
                                $is_level      = 1;
                            }
                        }
                    } else { //不是按规格
                        $this->debugOutput("不是按规格");
                        foreach ($distribution_detail_list as $detail) {
                            if ($detail->level == $distribution->level && $detail->goods_attr_id == 0) {
                                $first_price2  = $detail->commission_first;
                                $second_price2 = $detail->commission_second;
                                $third_price2  = $detail->commission_third;
                                $is_level      = 1;
                                break;
                            }
                        }
                    }
                } else {
                    $this->debugOutput("统一分销机制");
                    $distribution_level = DistributionLevel::findOne(['level' => $distribution->level, 'mall_id' => \Yii::$app->mall->id, 'is_delete' => 0]);
                    if ($distribution_level) { //找到分销商等级
                        $first_price2  = $distribution_level->first_price;
                        $second_price2 = $distribution_level->second_price;
                        $third_price2  = $distribution_level->third_price;
                        $price_type    = $distribution_level->price_type;
                        $is_level      = 1;
                    }
                }
                if ($is_level) {
                    $first_price  = $first_price2;
                    $second_price = $second_price2;
                    $third_price  = $third_price2;
                }else{
                    $first_price  = $first_price1;
                    $second_price = $second_price1;
                    $third_price  = $third_price1;
                    $price_type   = DistributionSetting::getValueByKey(DistributionSetting::PRICE_TYPE);
                }

                //公共的部分
                if ($price_type == 2) { //按固定金额
                    $this->debugOutput("按固定金额");
                    if ($user_level == 1) { //一级
                        $price = $first_price * $commonOrderDetail->num;
                    }
                    if ($user_level == 2) { //二级
                        $price = $second_price * $commonOrderDetail->num;
                    }
                    if ($user_level == 3) { //三级
                        $price = $third_price * $commonOrderDetail->num;
                    }
                }

                if ($price_type == 1) { //按百分比
                    $this->debugOutput("按百分比");
                    if ($user_level == 1) { //一级
                        $price = $first_price * $commonOrderDetail->price / 100;
                    }
                    if ($user_level == 2) { //二级
                        $price = $second_price * $commonOrderDetail->price / 100;;
                    }
                    if ($user_level == 3) { //三级
                        $price = $third_price * $commonOrderDetail->price / 100;;
                    }
                }

                $log->price = $price;

                $distributionUser = $distribution->user;

                //如果订单已支付 进行分佣操作
                if($order->is_pay && (!$orderDetail->is_refund || $orderDetail->refund_status == OrderDetail::REFUND_STATUS_SALES_END_REJECT)){
                    $this->debugOutput("订单为支付");

                    //分佣记录未处理，订单状态为待发货、待收货
                    //1.设置分佣记录为有效但未发放
                    //2.增加未结算佣金
                    if(0 == $log->status && in_array($order->status, [Order::STATUS_WAIT_DELIVER, Order::STATUS_WAIT_RECEIVE])){
                        $this->debugOutput("订单状态为待发货、待收货");
                        $log->status = 1;
                        if($distributionUser){
                            $this->incomeChange($distributionUser, $orderDetail, $log->price,true, true);
                        }
                    }

                    //佣记录已处理，订单状态为 取消待处理、已关闭的
                    //待结算佣金扣减
                    if(1 == $log->status && in_array($order->status, [Order::STATUS_CANCEL_WAIT, Order::STATUS_CLOSE])){
                        $this->debugOutput("订单状态为取消待处理、已关闭的");
                        if($distributionUser){
                            $this->incomeChange($distributionUser, $orderDetail, $log->price,true, false);
                        }
                        $log->status = -1;
                    }

                    //订单状态为 已完成、待评价、售后申请中、售后完成的 的
                    //开始佣金到账
                    if(in_array($order->status, [Order::STATUS_COMPLETE, Order::STATUS_WAIT_COMMENT, Order::STATUS_SALES_APPLY, Order::STATUS_SALES_COMPLETE])){
                        $this->debugOutput("订单状态为已完成、待评价、售后申请中、售后完成");
                        $log->is_price = 1;
                        $log->status   = 1;
                        $isDistribution = 1;
                        if($distributionUser){
                            $this->incomeChange($distributionUser, $orderDetail, $log->price,false, true);
                        }
                    }
                }

                if(!$distribution || !$distribution->save()){
                    throw new \Exception("分销商信息保存失败");
                }

                if(!$distributionUser || !$distributionUser->save()){
                    throw new \Exception("用户信息保存失败");
                }

                if($commonOrderDetail->is_distribution != $isDistribution){
                    $commonOrderDetail->is_distribution = $isDistribution;
                    if(!$commonOrderDetail->save()){
                        throw new \Exception("公共订单详情记录保存失败");
                    }
                }

                if (!$log->save()) {
                    throw new \Exception(json_encode($log->getErrors()));
                } else {
                    $distribution->frozen_price = 0;
                    $distribution->total_order  = 0;
                    $distribution->save();
                }
                $this->debugOutput("finished!");
            }

            $t->commit();
        }catch (\Exception $e){
            $t->rollBack();
            echo $e->getMessage() . "\n";
        }

        //更新优先级
        CommonOrderDetail::updateAll(["process_priority_level" => time()], [
            "id" => $this->common_order_detail_id
        ]);
    }

    private function incomeChange(User $user, OrderDetail $orderDetail, $price, $is_frozen, $is_add){

        $data = [
            "user_id"         => $user->id,
            "order_detail_id" => $orderDetail->id,
            "type"            => $is_add ? 1 : 2,
            "flag"            => $is_frozen ? 0 : 1,
            "from"            => 1
        ];
        $incomeLog = IncomeLog::findOne($data);
        if($incomeLog) return;

        if($is_frozen){
            if($is_add){
                $user->income_frozen += floatval($price);
                $desc = "来自订单[商品ID:".$orderDetail->goods_id."，详情ID:".$orderDetail->order_id."]的未结算佣金";
            }else{
                $user->income_frozen -= floatval($price);
                $desc = "订单[商品ID:".$orderDetail->goods_id."，详情ID:".$orderDetail->order_id."]的未结算佣金扣除";
            }
        }else{
            if($is_add){
                $user->income += floatval($price);
                $user->total_income += floatval($price);
                $desc = "来自订单[商品ID:".$orderDetail->goods_id."，详情ID:".$orderDetail->order_id."]的佣金收入";
            }else{
                $user->income -= floatval($price);
                $user->total_income -= floatval($price);
                $desc = "订单[商品ID:".$orderDetail->goods_id."，详情ID:".$orderDetail->order_id."]的佣金扣除";
            }
        }
        if(!$user->save()){
            throw new \Exception("用户收入信息更新失败");
        }

        $incomeLog = new IncomeLog();
        $incomeLog->mall_id         = $user->mall_id;
        $incomeLog->user_id         = $user->id;
        $incomeLog->order_detail_id = $orderDetail->id;
        $incomeLog->type            = $data['type'];
        $incomeLog->money           = $price;
        $incomeLog->desc            = $desc;
        $incomeLog->flag            = $data['flag'];
        $incomeLog->from            = $data['from'];
        $incomeLog->income          = $user->total_income;
        $incomeLog->created_at      = $orderDetail->created_at;
        if(!$incomeLog->save()){
            throw new \Exception("收入记录生成失败！");
        }

    }

    private function debugOutput($msg){
        if($this->is_debug){
            echo $this->common_order_detail_id . ":" . $msg . "\n";
        }
    }
}
