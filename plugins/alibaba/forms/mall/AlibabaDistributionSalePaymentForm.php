<?php

namespace app\plugins\alibaba\forms\mall;

use app\core\ApiCode;
use app\models\BaseModel;
use app\models\User;
use app\plugins\alibaba\models\AlibabaDistributionOrderDetail;
use app\plugins\alibaba\models\AlibabaDistributionOrderRefund;
use app\plugins\shopping_voucher\forms\common\ShoppingVoucherLogModifiyForm;

class AlibabaDistributionSalePaymentForm extends BaseModel
{

    public $id;
    public $act;
    public $content;
    public $aliRefundStatus;

    public function rules()
    {
        return [
            [['id', 'act'], 'required'],
            [['id'], 'integer'],
            [['content', 'aliRefundStatus'], 'safe']
        ];
    }

    public function save()
    {

        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }

        $t = \Yii::$app->getDb()->beginTransaction();
        try {
            $orderRefund = AlibabaDistributionOrderRefund::findOne($this->id);
            if (!$orderRefund) throw new \Exception("退款记录不存在");

            $OrderDetail = AlibabaDistributionOrderDetail::findOne($orderRefund->order_detail_id);
            if (!$OrderDetail || $OrderDetail->is_delete) {
                throw new \Exception("售后订单详情不存在");
            }

            if ($this->act == "cancel") { //拒绝
                if ($orderRefund->status != 'waitting')
                    throw new \Exception("无法拒绝操作");

                $orderRefund->status = 'cancel';
                $orderRefund->remark = $this->content;
                if (!$orderRefund->save())
                    throw new \Exception($orderRefund->getErrors());

                if ($OrderDetail->refund_status == 'finished')
                    throw new \Exception("无法拒绝操作");

                $OrderDetail->refund_status = 'refused';
                if (!$OrderDetail->save())
                    throw new \Exception(json_encode($OrderDetail->getErrors()));

                $msg = '拒绝打款';
            } elseif ($this->act == "paid") {
                if ($this->aliRefundStatus != 'refundsuccess')
                    throw new \Exception("1688退款未成功，暂时不能打款");

                if ($orderRefund->refund_type == 'waitting')
                    throw new \Exception("售后状态不正确");

                $orderRefund->status = 'paid';
                $orderRefund->remark = $this->content;
                if (!$orderRefund->save())
                    throw new \Exception($orderRefund->getErrors());

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
                        $modifyForm->add($user, true);
                        break;
                    default:
                }
                $msg = '打款成功';
            }
            $t->commit();
            return $this->returnApiResultData(ApiCode::CODE_SUCCESS, $msg);
        } catch (\Exception $e) {
            $t->rollBack();
            return $this->returnApiResultData(ApiCode::CODE_FAIL, $e->getMessage());
        }
    }
}