<?php
namespace app\component\jobs;

use app\component\efps\Efps;
use app\models\EfpsMchReviewInfo;
use app\models\EfpsTransferOrder;
use app\models\Order;
use app\plugins\mch\models\MchCheckoutOrder;
use yii\base\Component;
use yii\queue\JobInterface;

class EfpsTransferJob extends Component implements JobInterface{

    public function execute($queue){

        //待提交
        $transferOrder = EfpsTransferOrder::find()->andWhere("status IN (0,1)")->orderBy("updated_at ASC")->limit(1)->one();
        if(!$transferOrder) return;

        $t = \Yii::$app->getDb()->beginTransaction();
        try {
            if($transferOrder->order_type == "mch_checkout_order"){ //结账订单
                $checkoutOrder = MchCheckoutOrder::findOne([
                    "order_no" => $transferOrder->order_sn
                ]);
                if(!$checkoutOrder || !$checkoutOrder->is_pay){
                    throw new \Exception("订单[MchCheckoutOrder:". ($checkoutOrder ? $checkoutOrder->id : 0) ."]未支付");
                }

                $mchReviewInfo = EfpsMchReviewInfo::findOne([
                    "mch_id" => $checkoutOrder->mch_id
                ]);
            }elseif($transferOrder->order_type == "goods_order"){ //商品订单
                $order = Order::findOne(["order_no" => $transferOrder->order_sn]);
                if(!$order || !$order->is_pay){
                    throw new \Exception("订单[Order:" . ($order ? $order->id : 0) . "]未支付");
                }

                $mchReviewInfo = EfpsMchReviewInfo::findOne([
                    "mch_id" => $order->mch_id
                ]);
            }else{
                throw new \Exception("未知订单类型");
            }

            if(empty($mchReviewInfo) || $mchReviewInfo->status != 2){
                throw new \Exception("商家未审核通过");
            }

            if($transferOrder->status == 0){ //待提交状态
                $transferOrder->bankUserName    = $mchReviewInfo->paper_settleAccount;
                $transferOrder->bankCardNo      = $mchReviewInfo->paper_settleAccountNo;
                $transferOrder->bankName        = $mchReviewInfo->paper_openBank;
                $transferOrder->bankAccountType = 2; //对私
                $transferOrder->outTradeNo      = date("YmdHis") . rand(1000, 9999);
                $transferOrder->status          = 1; //切换提交状态
                $transferOrder->updated_at      = time();

                if(!$transferOrder->save()){
                    throw new \Exception(json_encode($transferOrder->getErrors()));
                }

                $res = \Yii::$app->efps->withdrawalToCard([
                    "customerCode"    => \Yii::$app->efps->getCustomerCode(),
                    "outTradeNo"      => $transferOrder->outTradeNo,
                    "notifyUrl"       => $transferOrder->notifyUrl,
                    "amount"          => $transferOrder->amount * 100,
                    "bankUserName"    => $transferOrder->bankUserName,
                    "bankCardNo"      => $transferOrder->bankCardNo,
                    "bankName"        => $transferOrder->bankName,
                    "bankAccountType" => $transferOrder->bankAccountType
                ]);

                if($res['code'] != Efps::CODE_SUCCESS){
                    throw new \Exception($res['msg']);
                }
            }else{ //已提交查询操作
                $res = \Yii::$app->efps->withdrawalToCardQuery([
                    "customerCode" => \Yii::$app->efps->getCustomerCode(),
                    "outTradeNo"   => $transferOrder->outTradeNo
                ]);
                if($res['code'] != Efps::CODE_SUCCESS){
                    throw new \Exception($res['msg']);
                }

                $transferOrder->updated_at = time();
                if($res['data']['returnCode'] == "0000" && $res['data']['payState'] == "00"){ //支付成功
                    $transferOrder->status = 2;
                }else{
                    $transferOrder->status = 3;
                    $transferOrder->remark = $res['data']['returnMsg'];
                }
                if(!$transferOrder->save()){
                    throw new \Exception(json_encode($transferOrder->getErrors()));
                }
            }
            $t->commit();
        }catch (\Exception $e){
            $t->rollBack();
            $transferOrder->remark = $e->getMessage();
            $transferOrder->fail_retry_count += 1;
            if($transferOrder->fail_retry_count > 3){ //错误次数太多 转账失败
                $transferOrder->status = 3;
            }
            $transferOrder->save();
        }
    }
}