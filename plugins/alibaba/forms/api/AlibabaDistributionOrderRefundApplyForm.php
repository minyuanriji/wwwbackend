<?php

namespace app\plugins\alibaba\forms\api;

use app\core\ApiCode;
use app\models\BaseModel;
use app\plugins\alibaba\models\AlibabaDistributionOrderDetail;
use app\plugins\alibaba\models\AlibabaDistributionOrderDetail1688;

class AlibabaDistributionOrderRefundApplyForm extends BaseModel{

    public $id_1688;
    public $reason_id;
    public $description;

    public function rules(){
        return [
            [['id_1688', 'reason_id', 'description'], 'required'],
            [['id_1688', 'reason_id'], 'integer'],
            [['description'], 'string']
        ];
    }

    public function doApply(){
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }

        try {

            $orderDetail1688 = AlibabaDistributionOrderDetail1688::findOne($this->id_1688);
            if(!$orderDetail1688){
                throw new \Exception("订单[ID:{$this->id_1688}]不存在");
            }

            $orderDetail = AlibabaDistributionOrderDetail::findOne($orderDetail1688->order_detail_id);
            if(!$orderDetail || $orderDetail->is_delete){
                throw new \Exception("订单[ID:{$this->id_1688}]异常");
            }

            $orderDetail->applyRefund([
                "reason_id"   => $this->reason_id,
                "description" => $this->description
            ]);

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg'  => "退款申请成功"
            ];

        }catch (\Exception $e){
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg'  => $e->getMessage(),
                /*'error' => [
                    'file' => $e->getFile(),
                    'line' => $e->getLine()
                ]*/
            ];
        }

    }
}