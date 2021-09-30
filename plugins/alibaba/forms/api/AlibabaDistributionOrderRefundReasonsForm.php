<?php

namespace app\plugins\alibaba\forms\api;

use app\core\ApiCode;
use app\models\BaseModel;
use app\plugins\alibaba\models\AlibabaApp;
use app\plugins\alibaba\models\AlibabaDistributionOrderDetail;
use app\plugins\alibaba\models\AlibabaDistributionOrderDetail1688;
use lin010\alibaba\c2b2b\api\GetRefundReasonList;
use lin010\alibaba\c2b2b\api\GetRefundReasonListResponse;
use lin010\alibaba\c2b2b\Distribution;

class AlibabaDistributionOrderRefundReasonsForm extends BaseModel{

    public $id_1688;
    public $after_sale_status;

    public function rules(){
        return [
            [['id_1688', 'after_sale_status'], 'required'],
            [['id_1688'], 'integer'],
        ];
    }

    public function get(){
        if(!$this->validate()){
            return $this->responseErrorInfo();
        }

        try {

            $status = [
                'refundWaitSellerSend'      => '售中等待买家发货',
                'refundWaitBuyerReceive'    => '售中等待买家收货',
                'refundBuyerReceived'       => '售中已收货（未确认完成交易）',
                'aftersaleBuyerNotReceived' => '售后未收货',
                'aftersaleBuyerReceived'    => '售后已收到货'
            ];
            if(!in_array($this->after_sale_status, array_keys($status))){
                throw new \Exception("售后状态参数”{$this->after_sale_status}“不正确");
            }

            $orderDetail1688 = AlibabaDistributionOrderDetail1688::findOne($this->id_1688);
            if(!$orderDetail1688){
                throw new \Exception("订单[ID:{$this->id_1688}]不存在");
            }

            if($orderDetail1688->status != "paid"){
                throw new \Exception("订单[ID:{$this->id_1688}]未支付");
            }

            $orderDetail = AlibabaDistributionOrderDetail::findOne($orderDetail1688->order_detail_id);
            if(!$orderDetail || $orderDetail->is_delete){
                throw new \Exception("订单[ID:{$this->id_1688}]异常");
            }
            if($orderDetail->refund_status != "none"){
                throw new \Exception("订单[ID:{$this->id_1688}]状态错误");
            }

            $app = AlibabaApp::findOne($orderDetail1688->app_id);
            if(!$app || $app->is_delete){
                throw new \Exception("应用[ID:{$this->app_id}]不存在");
            }

            $distribution = new Distribution($app->app_key, $app->secret);
            $res = $distribution->requestWithToken(new GetRefundReasonList([
                "orderId"       => $orderDetail1688->ali_order_id,
                "orderEntryIds" => json_encode([$orderDetail1688->ali_order_id]),
                "goodsStatus"   => $this->after_sale_status
            ]), $app->access_token);
            if(!empty($res->error)){
                throw new \Exception($res->error);
            }
            if(!$res instanceof GetRefundReasonListResponse){
                throw new \Exception("[GetRefundReasonListResponse]返回结果异常");
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'data' => [
                    'reasons' => $res->reasons
                ]
            ];
        }catch (\Exception $e){
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg'  => $e->getMessage()
            ];
        }
    }

}