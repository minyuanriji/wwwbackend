<?php

namespace app\plugins\alibaba\forms\mall;

use app\core\ApiCode;
use app\models\BaseModel;
use app\models\User;
use app\plugins\alibaba\models\AlibabaDistributionOrderDetail;
use app\plugins\alibaba\models\AlibabaDistributionOrderRefund;
use app\plugins\shopping_voucher\forms\common\ShoppingVoucherLogModifiyForm;

class AlibabaDistributionRefundPaidForm extends BaseModel{

    public $order_detail_id;
    public $refund_id;
    public $act;
    public $remark;

    public function rules()
    {
        return [
            [['order_detail_id', 'refund_id'], 'required'],
            [['act', 'remark'], 'safe']
        ];
    }

    public function save(){

        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }

        $t = \Yii::$app->getDb()->beginTransaction();
        try {

            $orderRefund = AlibabaDistributionOrderRefund::findOne($this->refund_id);
            if (!$orderRefund){
                throw new \Exception("[AlibabaDistributionOrderRefund] 退款记录不存在");
            }

            if ($orderRefund->status != 'waitting'){
                throw new \Exception("无法操作 {$orderRefund->status}");
            }

            $orderDetail = AlibabaDistributionOrderDetail::findOne($this->order_detail_id);
            if (!$orderDetail || $orderDetail->is_delete || $orderDetail->order_id != $orderRefund->order_id) {
                throw new \Exception("[AlibabaDistributionOrderDetail] 订单详情不存在");
            }

            if ($this->act == "cancel") { //拒绝
                $orderRefund->updated_at = time();
                $orderRefund->status     = 'cancel';
                $orderRefund->remark     = $this->remark;
                if (!$orderRefund->save()){
                    throw new \Exception($orderRefund->getErrors());
                }
                $msg = '拒绝打款';
            } elseif ($this->act == "paid") {
                $orderRefund->status = 'paid';
                $orderRefund->remark = $this->remark;
                if (!$orderRefund->save()){
                    throw new \Exception($orderRefund->getErrors());
                }

                $user = User::findOne($orderRefund->user_id);
                if(!$user || $user->is_delete){
                    throw new \Exception("用户不存在");
                }
                switch ($orderRefund->refund_type)
                {
                    case 'score':
                        break;
                    case 'integral':
                        break;
                    case 'money':
                        break;
                    case 'balance':
                        break;
                    case 'shopping_voucher':
                        $modifyForm = new ShoppingVoucherLogModifiyForm([
                            "money"       => $orderRefund->real_amount,
                            "desc"        => "1688订单退款返还购物券",
                            "source_id"   => $orderRefund->id,
                            "source_type" => '1688_distribution_order_detail_refund'
                        ]);
                        $modifyForm->add($user, false);
                        break;
                    default:
                }
                $msg = '打款成功';
            }

            //如果订单已无待操作记录，设置状态未已完成
            $isFinished = 0;
            $count = AlibabaDistributionOrderRefund::find()->andWhere([
                "AND",
                ["status" => "waitting"],
                ["order_id" => $orderRefund->order_id],
                "order_detail_id='".$this->order_detail_id."' OR order_detail_id=0"
            ])->count();
            if(!$count && $orderDetail->refund_status == "agree"){
                $orderDetail->is_refund     = 1;
                $orderDetail->refund_status = "finished";
                $orderDetail->updated_at    = time();
                if(!$orderDetail->save()){
                    throw new \Exception($this->responseErrorMsg($orderDetail));
                }
                $isFinished = 1;
            }

            $t->commit();
            return $this->returnApiResultData(ApiCode::CODE_SUCCESS, $msg, [
                "is_finished" => $isFinished
            ]);
        } catch (\Exception $e) {
            $t->rollBack();
            return $this->returnApiResultData(ApiCode::CODE_FAIL, $e->getMessage());
        }
    }
}