<?php

namespace app\commands\alibaba_distribution_order_task;

use app\forms\common\UserBalanceModifyForm;
use app\models\BalanceLog;
use app\models\User;
use app\plugins\alibaba\models\AlibabaApp;
use app\plugins\alibaba\models\AlibabaDistributionOrderDetail;
use app\plugins\alibaba\models\AlibabaDistributionOrderDetail1688;
use app\plugins\alibaba\models\AlibabaDistributionOrderRefund;
use app\plugins\shopping_voucher\forms\common\ShoppingVoucherLogModifiyForm;
use lin010\alibaba\c2b2b\api\OrderProtocolPay;
use lin010\alibaba\c2b2b\api\OrderProtocolPayResponse;
use lin010\alibaba\c2b2b\Distribution;
use yii\base\Action;


class OrderPaidAction extends Action{

    /**
     * 阿里巴巴分销订单付款处理任务
     */
    public function run(){
        $this->controller->commandOut(date("Y/m/d H:i:s") . " OrderPaidAction start");
        while(true){
            $orderDetail1688 = $this->getOne();
            if(!$orderDetail1688) continue;

            $error = null;
            $t = \Yii::$app->db->beginTransaction();
            try {
                $this->doPaid($orderDetail1688);
                $t->commit();
            }catch (\Exception $e){
                $t->rollBack();
                $this->controller->commandOut(implode("\n", [$e->getMessage(), "File:" . $e->getFile(), "Line:" . $e->getLine()]));
                $error = "Error:" . $e->getMessage();
            }
            //如果执行不成功
            if(!empty($error)){
                $orderDetail1688->do_error = $e->getMessage();
                $orderDetail1688->try_count += 1;
                if($orderDetail1688->try_count > 3){ //错误次数超过3次，执行订单退款
                    //$this->doRefund($orderDetail1688);
                    $orderDetail1688->save();
                }else{
                    $orderDetail1688->save();
                }
            }

            $this->controller->sleep(1);
        }
    }

    /**
     * 获取一条待处理数据
     * @return AlibabaDistributionOrderDetail1688|null
     */
    private function getOne(){
        try {
            $orderDetail1688 = AlibabaDistributionOrderDetail1688::find()->where(["status" => "unpaid"])->orderBy("updated_at ASC")->one();
            if($orderDetail1688){
                $orderDetail1688->updated_at = time();
                $orderDetail1688->save();
            }
        }catch (\Exception $e){
            $this->controller->commandOut(date("Y/m/d H:i:s") . " AlibabaDistributionOrderTask::OrderPaidAction start");
            $orderDetail1688 = null;
        }
        return $orderDetail1688;
    }

    /**
     * 支付宝协议代扣
     * @param AlibabaDistributionOrderDetail1688 $orderDetail1688
     */
    private function doPaid(AlibabaDistributionOrderDetail1688 $orderDetail1688){

        //更新状态
        $orderDetail1688->status = "paid";
        if(!$orderDetail1688->save()){
            throw new \Exception(json_encode($orderDetail1688->getErrors()));
        }

        $app = AlibabaApp::findOne($orderDetail1688->app_id);
        if(!$app || $app->is_delete){
            throw new \Exception("应用[ID:{$orderDetail1688->app_id}]不存在");
        }
        $distribution = new Distribution($app->app_key, $app->secret);
        $res = $distribution->requestWithToken(new OrderProtocolPay([
            "orderId" => $orderDetail1688->ali_order_id
        ]), $app->access_token);
        if(!empty($res->error)){
            throw new \Exception($res->error);
        }
        if(!$res instanceof OrderProtocolPayResponse){
            throw new \Exception("[OrderProtocolPayResponse]返回结果异常");
        }
        if(!$res->success){
            throw new \Exception($res->message . " " . $res->code);
        }
    }

    /**
     * 执行协议代扣失败，退款
     * @param AlibabaDistributionOrderDetail1688 $orderDetail1688
     */
    private function doRefund(AlibabaDistributionOrderDetail1688 $orderDetail1688){
        $t = \Yii::$app->db->beginTransaction();
        try {
            //更新状态
            $orderDetail1688->status = "invalid";
            if(!$orderDetail1688->save()){
                throw new \Exception(json_encode($orderDetail1688->getErrors()));
            }

            $orderDetail = AlibabaDistributionOrderDetail::findOne($orderDetail1688->order_detail_id);
            $orderDetail && $orderDetail->agreeRefund(false, "交易异常，执行订单自动退款操作");

            $user = User::findOne($orderDetail1688->user_id);

            //如果有余额、购物券的退款，执行自动退款操作
            $refunds = AlibabaDistributionOrderRefund::find()->where([
                "status"          => "waitting",
                "order_id"        => $orderDetail1688->order_id,
                "order_detail_id" => $orderDetail1688->order_detail_id
            ])->andWhere(["IN", "refund_type", ["balance", "shopping_voucher"]])->all();
            if($refunds){
                foreach($refunds as $refund){
                    $refund->status     = "paid";
                    $refund->updated_at = time();
                    if(!$refund->save()){
                        throw new \Exception(json_encode($refund->getErrors()));
                    }

                    if($refund->refund_type == "balance"){ //退还余额
                        $modifyForm = new UserBalanceModifyForm([
                            "type"        => BalanceLog::TYPE_ADD,
                            "money"       => $refund->real_amount,
                            "source_id"   => $orderDetail->id,
                            "source_type" => "1688_distribution_order_detail_refund",
                            "desc"        => "交易失败，退还余额",
                            "custom_desc" => ""
                        ]);
                        $modifyForm->modify($user);
                    }

                    if($refund->refund_type == "shopping_voucher") { //退还购物券
                        $modifyForm = new ShoppingVoucherLogModifiyForm([
                            "money"       => $refund->real_amount,
                            "desc"        => "交易失败，退还购物券",
                            "source_id"   => $orderDetail->id,
                            "source_type" => "1688_distribution_order_detail_refund"
                        ]);
                        $modifyForm->add($user);
                    }
                }
            }

            $t->commit();
        }catch (\Exception $e){
            $t->rollBack();
            $this->controller->commandOut(implode("\n", [$e->getMessage(), $e->getFile(), $e->getLine()]));
        }

    }
}