<?php

namespace app\plugins\alibaba\forms\api;

use app\core\ApiCode;
use app\models\User;
use app\plugins\alibaba\models\AlibabaDistributionOrder;
use app\plugins\alibaba\models\AlibabaDistributionOrderDetail;
use app\plugins\shopping_voucher\forms\common\ShoppingVoucherLogModifiyForm;

class AlibabaDistributionOrderDoSubmitForm extends AlibabaDistributionOrderForm {

    public function rules(){
        return array_merge(parent::rules(), [
            [["use_shopping_voucher", "use_address_id"], "required"]
        ]);
    }

    public function submit(){
        if(!$this->validate()){
            return $this->responseErrorInfo();
        }

        $t = \Yii::$app->db->beginTransaction();
        try {

            $data = $this->getData();

            if(!$data['user_address']){
                throw new \Exception("未选择收货地址");
            }

            $user = User::findOne(\Yii::$app->user->id);

            foreach ($data['list'] as $orderItem) {
                $order = new AlibabaDistributionOrder();
                $order->mall_id                       = \Yii::$app->mall->id;
                $order->user_id                       = $user->getId();
                $order->order_no                      = static::getOrderNo('S');;
                $order->total_price                   = $orderItem['total_price'];
                $order->total_pay_price               = $orderItem['total_price'];
                $order->total_goods_price             = $orderItem['total_price'];
                $order->express_original_price        = $orderItem['express_price'];
                $order->express_price                 = $orderItem['express_price'];
                $order->total_goods_original_price    = $orderItem['total_goods_original_price'];

                //购物券抵扣
                $order->shopping_voucher_use_num      = $orderItem['shopping_voucher_use_num'];
                $order->shopping_voucher_decode_price = $orderItem['shopping_voucher_decode_price'];

                $order->name                          = !empty($data['user_address']['name']) ? $data['user_address']['name'] : "";
                $order->mobile                        = !empty($data['user_address']['mobile']) ? $data['user_address']['mobile'] : "";
                $order->address                       = $data['user_address']['province']
                                                        . ' '
                                                        . $data['user_address']['city']
                                                        . ' '
                                                        . $data['user_address']['district']
                                                        . ' '
                                                        . $data['user_address']['town']
                                                        . ' '
                                                        . $data['user_address']['detail'];
                $order->address_id                    = $data['user_address']['id'];
                $order->province_id                   = $data['user_address']['province'];
                $order->remark                        = $this->remark;
                $order->token                         = \Yii::$app->security->generateRandomString();
                $order->is_pay                        = 0;
                $order->pay_type                      = 0;
                $order->is_send                       = 0;
                $order->is_confirm                    = 0;
                $order->status                        = 0;

                if (!$order->save()) {
                    throw new \Exception($this->responseErrorMsg($order));
                }

                //生成订单详情
                foreach ($orderItem['goods_list'] as $goodsItem){
                    $this->extraOrderDetail($order, $goodsItem);
                }

                //扣除购物券
                if($order->shopping_voucher_use_num > 0){
                    $modifyForm = new ShoppingVoucherLogModifiyForm([
                        "money"       => $order->shopping_voucher_use_num,
                        "desc"        => "订单(" . $order->id. ")创建扣除购物券：" . $order->shopping_voucher_use_num,
                        "source_id"   => $order->id,
                        "source_type" => "target_alibaba_distribution_order"
                    ]);
                    $modifyForm->sub($user);
                }
            }

            $t->commit();

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'data' => [
                    'token' => $order->token
                ]
            ];
        }catch (\Exception $e){
            $t->rollBack();
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg'  => $e->getMessage()
            ];
        }
    }

    /**
     * 订单扩展
     * @param AlibabaDistributionOrder $order
     * @param $goodsItem
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function extraOrderDetail(AlibabaDistributionOrder $order, $goodsItem){
        $orderDetail                        = new AlibabaDistributionOrderDetail();
        $orderDetail->mall_id               = $order->mall_id;
        $orderDetail->order_id              = $order->id;
        $orderDetail->goods_id              = $goodsItem['id'];
        $orderDetail->sku_id                = $goodsItem['sku_id'];
        $orderDetail->ali_sku               = $goodsItem['ali_sku'];
        $orderDetail->sku_labels            = json_encode($goodsItem['sku_labels']);
        $orderDetail->num                   = $goodsItem['num'];
        $orderDetail->unit_price            = $goodsItem['price'];
        $orderDetail->total_original_price  = $goodsItem['total_original_price'];
        $orderDetail->total_price           = $goodsItem['total_price'];

        //购物券抵扣
        if(isset($goodsItem['use_shopping_voucher_decode_price'])){
            $orderDetail->shopping_voucher_decode_price = $goodsItem['use_shopping_voucher_decode_price'];
        }
        if(isset($goodsItem['use_shopping_voucher_num'])){
            $orderDetail->shopping_voucher_num = $goodsItem['use_shopping_voucher_num'];
        }
        if(!$orderDetail->save()){
            throw new \Exception($this->responseErrorMsg($orderDetail));
        }
    }
}