<?php

namespace app\plugins\alibaba\forms\mall;

use app\core\ApiCode;
use app\models\BaseModel;
use app\models\User;
use app\plugins\alibaba\models\AlibabaDistributionGoodsList;
use app\plugins\alibaba\models\AlibabaDistributionOrderDetail;
use app\plugins\alibaba\models\AlibabaDistributionOrderDetail1688;
use app\plugins\alibaba\models\AlibabaDistributionOrderRefund;

class AlibabaDistributionOrderDetailForm extends BaseModel{

    public $id;

    public function rules(){
        return [
            [['id'], 'required']
        ];
    }

    public function getDetail(){
        if(!$this->validate()){
            return $this->responseErrorInfo();
        }

        try {

            $orderDetail = AlibabaDistributionOrderDetail::findOne($this->id);
            if(!$orderDetail){
                throw new \Exception("[AlibabaDistributionOrderDetail]订单详情不存在");
            }

            $order = $orderDetail->order;
            if(!$order){
                throw new \Exception("[AlibabaDistributionOrder]订单信息不存在");
            }

            $detail1688 = AlibabaDistributionOrderDetail1688::findOne(["order_detail_id" => $orderDetail->id]);
            if(!$detail1688){
                throw new \Exception("[AlibabaDistributionOrderDetail1688] 1688订单信息不存在");
            }

            $goods = AlibabaDistributionGoodsList::findOne($orderDetail->goods_id);
            if(!$goods){
                throw new \Exception("[AlibabaDistributionGoodsList] 商品信息不存在");
            }

            $user = User::findOne($detail1688->user_id);
            if(!$user){
                throw new \Exception("[User] 支付用户不存在");
            }

            $detail['order']         = $order->getAttributes();
            $detail['detail']        = $orderDetail->getAttributes();
            $detail['detail_1688']   = $detail1688->getAttributes();
            $detail['ali_orderdata'] = @json_decode($detail1688->ali_orderdata, true);
            $detail['goods']         = $goods->getAttributes();
            $detail['user']          = $user->getAttributes();

            $skuLabels = @json_decode($detail['detail']['sku_labels'], true);
            $detail['detail']['sku_labels'] = implode("，", $skuLabels);

            if(!isset($detail['ali_orderdata']['baseInfo']['refundStatus'])){
                $detail['ali_orderdata']['baseInfo']['refundStatus'] = "";
            }

            $refundDatas = AlibabaDistributionOrderRefund::find()->asArray()->where(["order_detail_id" => $orderDetail->id])->all();

            $detail['refund_datas'] = $refundDatas ? $refundDatas : [];

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'data' => $detail
            ];
        }catch (\Exception $e){
            return ['code' => ApiCode::CODE_FAIL, 'msg' => $e->getMessage()];
        }
    }

}