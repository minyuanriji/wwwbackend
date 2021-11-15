<?php

namespace app\plugins\alibaba\forms\api;

use app\core\ApiCode;
use app\logic\AppConfigLogic;
use app\models\BaseModel;
use app\models\OrderRefund;
use app\plugins\alibaba\helpers\AliRefundHelper;
use app\plugins\alibaba\models\AlibabaApp;
use app\plugins\alibaba\models\AlibabaDistributionGoodsList;
use app\plugins\alibaba\models\AlibabaDistributionOrderDetail;
use app\plugins\alibaba\models\AlibabaDistributionOrderDetail1688;
use lin010\alibaba\c2b2b\api\GetOrderInfo;
use lin010\alibaba\c2b2b\api\GetOrderInfoResponse;
use lin010\alibaba\c2b2b\api\GetRefundReasonList;
use lin010\alibaba\c2b2b\api\GetRefundReasonListResponse;
use lin010\alibaba\c2b2b\Distribution;

class DistributionOrderRefundToRefundSubmitForm extends BaseModel{

    public $order_detail_id;

    public function rules(){
        return [
            [['order_detail_id'], 'required']
        ];
    }

    public function getDetail(){
        if(!$this->validate()){
            return $this->responseErrorInfo();
        }

        try {

            $returnData = [];

            $orderDetail = AlibabaDistributionOrderDetail::findOne($this->order_detail_id);
            if(!$orderDetail || $orderDetail->is_delete){
                throw new \Exception("订单不存在");
            }

            $refundData = !empty($orderDetail->refund_json_data) ? json_decode($orderDetail->refund_json_data, true) : [];
            $skuLabels = $orderDetail['sku_labels'] ? @json_decode($orderDetail['sku_labels'], true) : [];

            $orderDetail1688 = AlibabaDistributionOrderDetail1688::findOne(["order_detail_id" => $orderDetail->id]);

            $goods = AlibabaDistributionGoodsList::findOne($orderDetail->goods_id);
            if(!$goods){
                throw new \Exception("商品不存在");
            }

            $goodsInfo['name']       = $goods->name;
            $goodsInfo['sku_labels'] = $skuLabels ? implode(",", $skuLabels) : "";
            $goodsInfo['num']        = $orderDetail->num;
            $goodsInfo['pic_url']    = $goods->cover_url;
            $goodsInfo['shopping_voucher_num'] = $orderDetail->shopping_voucher_num;

            $detail['refund_status'] = $orderDetail->refund_status;
            $detail['refund_data']   = $refundData;
            $detail['is_refund']     = $orderDetail->is_refund;
            $detail['goods_info']    = $goodsInfo;
            $detail['refund_shopping_voucher_num'] = $orderDetail->shopping_voucher_num;
            $detail["refund_reason_list"] = AppConfigLogic::getRefundReasonConfig();

            $data = $detail;

            $returnData["detail"] = $data;
            return $this->returnApiResultData(ApiCode::CODE_SUCCESS,'请求成功', $returnData);
        }catch (\Exception $e){
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg'  => $e->getMessage()
            ];
        }
    }
}