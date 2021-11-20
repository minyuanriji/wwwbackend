<?php

namespace app\plugins\alibaba\models;

use app\models\BaseActiveRecord;
use lin010\alibaba\c2b2b\api\CreateRefund;
use lin010\alibaba\c2b2b\api\CreateRefundResponse;
use lin010\alibaba\c2b2b\api\GetOrderInfo;
use lin010\alibaba\c2b2b\api\GetOrderInfoResponse;
use lin010\alibaba\c2b2b\Distribution;

class AlibabaDistributionOrderDetail extends BaseActiveRecord{

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%plugin_alibaba_distribution_order_detail}}';
    }

    public function rules()
    {
        return [
            [['mall_id', 'app_id', 'order_id', 'goods_id', 'num', 'unit_price', 'total_original_price', 'total_price', 'created_at', 'updated_at'], 'required'],
            [['is_delete', 'deleted_at', 'is_refund'], 'integer'],
            [['shopping_voucher_decode_price', 'shopping_voucher_num'], 'number', 'min' => 0],
            [['sku_labels', 'refund_status', 'ali_num', 'sku_id', 'ali_spec_id', 'ali_sku'], 'safe']
        ];
    }

    /**
     * 申请退款
     * @param $data
     * @throws \Exception
     */
    public function applyRefund($data){
        if($this->refund_status != "none"){
            throw new \Exception("当前状态无法申请退款");
        }

        $order = $this->order;
        if(!$order || $order->is_delete || !$order->is_pay){
            throw new \Exception("订单不存在或未支付");
        }

        $data['apply_time'] = date("Y-m-d H:i:s", time());
        try {
            $this->refund_status    = "apply";
            $this->refund_json_data = json_encode($data);
            if(!$this->save()){
                throw new \Exception(json_encode($this->getErrors()));
            }
        }catch (\Exception $e){
            throw $e;
        }
    }

    /**
     * 同意退款
     * @param boolean $trans
     * @param string $desc
     * @param integer $refund_express 是否退运费
     * @throws \yii\db\Exception
     */
    public function agreeRefund($trans = false, $desc = "", $refund_express = 0){
        if($this->refund_status != "apply"){
            throw new \Exception("只有申请中状态才允许同意退款操作");
        }

        $order = $this->order;
        if(!$order || $order->is_delete || !$order->is_pay){
            throw new \Exception("订单不存在或未支付");
        }

        $trans && ($t = \Yii::$app->db->beginTransaction());
        try {


            $detail1688 = AlibabaDistributionOrderDetail1688::findOne(["order_detail_id" => $this->id]);
            if(!$detail1688){
                throw new \Exception("[AlibabaDistributionOrderDetail1688] 1688订单信息不存在");
            }

            //用户提交的退款申请信息
            $refundData = @json_decode($this->refund_json_data, true);

            //获取1688该订单信息
            $app = AlibabaApp::findOne($detail1688->app_id);
            $distribution = new Distribution($app->app_key, $app->secret);
            $res = $distribution->requestWithToken(new GetOrderInfo([
                "webSite" => "1688",
                "orderId" => $detail1688->ali_order_id
            ]), $app->access_token);
            if(!$res instanceof GetOrderInfoResponse){
                throw new \Exception("[GetOrderInfoResponse]返回结果异常");
            }
            if($res->error){
                throw new \Exception($res->error);
            }
            $status = $res->result['baseInfo']['status'];
            $orderData1688 = $res->result;

            //TODO 暂时由人工操作退款 阿里巴巴提交退款申请
            /*
             if($status != "waitsellersend"){
                throw new \Exception("只允许已付款未发货的订单退款 [{$status}]");
            }
             $res = $distribution->requestWithToken(new CreateRefund([
                "orderId"        => $detail1688->ali_order_id,
                "orderEntryIds"  => json_encode([$detail1688->ali_order_id]),
                "disputeRequest" => "refund",
                "applyPayment"   => $res->result['baseInfo']['totalAmount'] * 100,
                "applyCarriage"  => 0,
                "applyReasonId"  => isset($refundData['reason_id']) ? $refundData['reason_id'] : "",
                "description"    => "买家申请退款：" . (isset($refundData['description']) ? $refundData['description'] : ""),
                "goodsStatus"    => "refundWaitSellerSend"
            ]), $app->access_token);
            if(!$res instanceof CreateRefundResponse){
                throw new \Exception("[CreateRefundResponse]返回结果异常");
            }
            if($res->error){
                throw new \Exception($res->error);
            }*/

            //TODO 暂时由人工操作退款 更新1688订单信息
            /*$detail1688->ali_refund_id = isset($res->refundId) ? $res->refundId : "";
            $detail1688->ali_orderdata = json_encode($orderData1688);
            $detail1688->updated_at    = time();
            if(!$detail1688->save()){
                throw new \Exception(json_encode($detail1688->getErrors()));
            }*/

            //更新订单详情，设置状态为已同意
            $this->refund_status = "agree";
            $this->updated_at    = time();
            if(!$this->save()){
                throw new \Exception(json_encode($this->getErrors()));
            }

            //判断如果所有商品都申请了退款，关闭订单
            $existCount = (int)static::find()->where([
                "order_id"  => $this->order_id,
                "is_delete" => 0
            ])->andWhere(["IN", "refund_status", ['refused','none','apply']])->count();
            if($existCount <= 0){
                //关闭订单
                $order->is_closed    = 1;
                $order->updated_at   = time();
                $order->close_reason = $desc;
                if(!$order->save()){
                    throw new \Exception(json_encode($order->getErrors()));
                }
            }

            $commonRefundData = [
                "mall_id"         => $this->mall_id,
                "order_id"        => $this->order_id,
                "order_detail_id" => $this->id,
                "user_id"         => $order->user_id,
                "status"          => "waitting",
                "created_at"      => time(),
                "updated_at"      => time(),
                "remark"          => "",
            ];

            //退运费
            if($refund_express){
                $expressCommonRefundData = array_merge($commonRefundData, ["order_detail_id" => 0]);

                //退现金（余额、支付宝或微信）
                if($order->pay_type == 3 && $order->express_price > 0) { //余额支付
                    if(!AlibabaDistributionOrderRefund::findOne([
                        "mall_id"         => $this->mall_id,
                        "order_id"        => $this->order_id,
                        "user_id"         => $order->user_id,
                        "refund_type"     => "balance",
                        "order_detail_id" => 0,
                    ])){
                        $refund = new AlibabaDistributionOrderRefund(array_merge($expressCommonRefundData, [
                            "refund_type"   => "balance",
                            "refund_amount" => $order->express_price,
                            "real_amount"   => $order->express_price
                        ]));
                        if(!$refund->save()){
                            throw new \Exception(json_encode($refund->getErrors()));
                        }
                    }
                }elseif($order->express_price > 0){
                    if(!AlibabaDistributionOrderRefund::findOne([
                        "mall_id"         => $this->mall_id,
                        "order_id"        => $this->order_id,
                        "user_id"         => $order->user_id,
                        "refund_type"     => "money",
                        "order_detail_id" => 0,
                    ])){
                        $refund = new AlibabaDistributionOrderRefund(array_merge($expressCommonRefundData, [
                            "refund_type"   => "money",
                            "refund_amount" => $order->express_price,
                            "real_amount"   => $order->express_price
                        ]));
                        if(!$refund->save()){
                            throw new \Exception(json_encode($refund->getErrors()));
                        }
                    }
                }

                //退购物券
                if($order->shopping_voucher_express_use_num > 0){
                    if(!AlibabaDistributionOrderRefund::findOne([
                        "mall_id"         => $this->mall_id,
                        "order_id"        => $this->order_id,
                        "user_id"         => $order->user_id,
                        "refund_type"     => "shopping_voucher",
                        "order_detail_id" => 0,
                    ])){
                        $refund = new AlibabaDistributionOrderRefund(array_merge($expressCommonRefundData, [
                            "refund_type"   => "shopping_voucher",
                            "refund_amount" => $order->shopping_voucher_express_use_num,
                            "real_amount"   => $order->shopping_voucher_express_use_num
                        ]));
                        if(!$refund->save()){
                            throw new \Exception(json_encode($refund->getErrors()));
                        }
                    }
                }
            }

            //退现金（余额、支付宝或微信）
            if($this->total_price > 0){
                if($order->pay_type == 3){ //余额支付
                    $refund = new AlibabaDistributionOrderRefund(array_merge($commonRefundData, [
                        "refund_type"   => "balance",
                        "refund_amount" => $this->total_price,
                        "real_amount"   => $this->total_price,
                    ]));
                }else{
                    $refund = new AlibabaDistributionOrderRefund(array_merge($commonRefundData, [
                        "refund_type"   => "money",
                        "refund_amount" => $this->total_price,
                        "real_amount"   => $this->total_price,
                    ]));
                }
                if(!$refund->save()){
                    throw new \Exception(json_encode($refund->getErrors()));
                }
                $moneyRefund = $refund;
            }

            //退购物券
            if($this->shopping_voucher_num > 0){
                $refund = new AlibabaDistributionOrderRefund(array_merge($commonRefundData, [
                    "refund_type"   => "shopping_voucher",
                    "refund_amount" => $this->shopping_voucher_num,
                    "real_amount"   => $this->shopping_voucher_num,
                ]));
                if(!$refund->save()){
                    throw new \Exception(json_encode($refund->getErrors()));
                }
                $shoppingVoucherRefund = $refund;
            }

            $trans && $t->commit();

            $refundStatus = $res->result['baseInfo']['refundStatus'] ?? '';
            return [
                'orderStatus' => $status,
                'refundStatus' => $refundStatus,
                'moneyRefund' => $moneyRefund ?? '',
                'shoppingVoucherRefund' => $shoppingVoucherRefund ?? '',
            ];
        }catch (\Exception $e){
            $trans && $t->rollBack();
            throw $e;
        }
    }

    /**
     * 获取订单记录
     * @return \yii\db\ActiveQuery
     */
    public function getOrder(){
        return $this->hasOne(AlibabaDistributionOrder::class, ["id" => "order_id"]);
    }

    /**
     * 获取商品
     * @return \yii\db\ActiveQuery
     */
    public function getGoods(){
        return $this->hasOne(AlibabaDistributionGoodsList::class, ["id" => "goods_id"]);
    }
}