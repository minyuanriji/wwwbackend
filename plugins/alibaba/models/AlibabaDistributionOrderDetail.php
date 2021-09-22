<?php

namespace app\plugins\alibaba\models;

use app\models\BaseActiveRecord;

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
            [['sku_labels', 'refund_status', 'sku_id', 'ali_spec_id', 'ali_sku'], 'safe']
        ];
    }

    /**
     * 同意退款
     * @param boolean $trans
     * @param string $desc
     * @throws \yii\db\Exception
     */
    public function agreeRefund($trans = false, $desc = ""){
        if($this->refund_status != "none")
            return;

        $trans && ($t = \Yii::$app->db->beginTransaction());
        try {
            $this->refund_status = "agree";
            $this->updated_at    = time();
            if(!$this->save()){
                throw new \Exception(json_encode($this->getErrors()));
            }

            $order = $this->order;

            //判断如果所有商品都申请了退款，退还运费
            $existCount = (int)static::find()->where([
                "order_id"  => $this->order_id,
                "is_delete" => 0
            ])->andWhere(["IN", "refund_status", ['refused','none','apply']])->count();
            if($existCount <= 0){
                $this->total_price += $order->express_price;
                $this->shopping_voucher_num += $order->shopping_voucher_express_use_num;

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
            }

            $trans && $t->commit();
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
}