<?php

namespace app\plugins\alibaba\forms\api;

use app\core\ApiCode;
use app\models\BaseModel;
use app\plugins\alibaba\models\AlibabaDistributionOrderDetail;
use app\plugins\alibaba\models\AlibabaDistributionOrderDetail1688;

class AlibabaDistributionOrderRefundApplyForm extends BaseModel{

    public $order_detail_id;
    public $is_receipt;
    public $pic_list;
    public $reason;
    public $refund_type;
    public $remark;

    public function rules(){
        return [
            [['order_detail_id',  'reason'], 'required'],
            [['remark', 'reason'], 'string']
        ];
    }

    public function doApply(){
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }

        try {

            $orderDetail = AlibabaDistributionOrderDetail::findOne($this->order_detail_id);
            if(!$orderDetail || $orderDetail->is_delete){
                throw new \Exception("订单[ID:{$this->order_detail_id}]异常");
            }

            $orderDetail->applyRefund([
                "reason_id"   => -1,
                "description" => $this->reason,
                "remark"      => $this->remark
            ]);

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg'  => "退款申请成功"
            ];

        }catch (\Exception $e){
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg'  => $e->getMessage()
            ];
        }
    }
}