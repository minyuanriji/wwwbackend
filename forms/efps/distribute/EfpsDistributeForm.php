<?php
namespace app\forms\efps\distribute;

use app\core\ApiCode;
use app\mch\forms\mch\MchAccountModifyForm;
use app\mch\forms\mch\MchAccountWithdraw;
use app\models\BaseModel;
use app\models\EfpsMchReviewInfo;
use app\models\Order;
use app\models\User;
use app\plugins\mch\models\Mch;
use app\plugins\mch\models\MchCheckoutOrder;

class EfpsDistributeForm extends BaseModel{

    public $order_sn;
    public $order_type;
    public $pay_user_id;

    public function rules(){
        return [
            [['order_sn', 'order_type'], 'required']
        ];
    }

    public function save(){
        if(!$this->validate()){
            return $this->responseErrorInfo();
        }

        $t = \Yii::$app->getDb()->beginTransaction();
        try {

            $mch    = null;
            $amount = 0;
            $desc   = "";
            if($this->order_type == "goods_order"){ //商品订单
                $order = Order::findOne([
                    "order_no"  => $this->order_sn,
                    "is_pay"    => 1,
                    "is_delete" => 0
                ]);
                if(!$order){
                    throw new \Exception("订单不存在");
                }
                if($order->mch_id){
                    $mch = Mch::findOne([
                        "id"            => $order->mch_id,
                        "review_status" => 1,
                        "is_delete"     => 0
                    ]);
                }
                $amount = $order->total_goods_original_price;
                $desc   = "来自商品订单[".$this->order_sn."]的收入";
            }elseif($this->order_type == "mch_checkout_order"){ //结账订单
                $checkoutOrder = MchCheckoutOrder::findOne([
                    "order_no"  => $this->order_sn,
                    "is_pay"    => 1,
                    "is_delete" => 0
                ]);
                if(!$checkoutOrder){
                    throw new \Exception("结账订单不存在");
                }
                if($checkoutOrder->mch_id){
                    $mch = Mch::findOne([
                        "id"            => $checkoutOrder->mch_id,
                        "review_status" => 1,
                        "is_delete"     => 0
                    ]);
                }
                $amount = $checkoutOrder->order_price;
                if ($this->pay_user_id) {
                    $user = User::findOne([
                        "id" => $this->pay_user_id,
                    ]);
                    if ($user) {
                        $desc   = "来自结账订单[".$user->nickname."]的付款";
                    } else {
                        $desc   = "来自结账订单[".$this->order_sn."]的收入";
                    }
                } else {
                    $desc   = "来自结账订单[".$this->order_sn."]的收入";
                }
            }

            if(!$mch){
                throw new \Exception("商户不存在");
            }

            if($amount <= 0){
                throw new \Exception("金额不能小于0");
            }

            //计算要打给商家的钱
            $serviceFeeRate = max(0, min(100, (int)$mch->transfer_rate));
            $serviceFee = ($serviceFeeRate/100) * floatval($amount);
            $amount = $amount - $serviceFee;

            //修改商家帐户
            $res = MchAccountModifyForm::modify($mch, $amount, $desc, true);
            if($res['code'] != ApiCode::CODE_SUCCESS){
                throw new \Exception($res['msg']);
            }

            $t->commit();

            $reviewInfo = EfpsMchReviewInfo::findOne([
                'mch_id' => $mch->id
            ]);
            if($reviewInfo && $reviewInfo['paper_settleTarget'] == 1){ //自动提现 TODO
                $res = MchAccountWithdraw::efpsBank($mch, $amount - 0.5, "系统自动提现操作");
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '保存成功'
            ];
        }catch (\Exception $e){
            $t->rollBack();

            return [
                'code' => ApiCode::CODE_FAIL,
                'msg' => $e->getMessage()
            ];
        }

    }

    /**
     * 商品订单
     * @param Order $order
     */
    public static function goodsOrder(Order $order){
        return (new EfpsDistributeForm([
            "order_sn"   => $order->order_no,
            "order_type" => "goods_order"
        ]))->save();
    }

    /**
     * 结账订单
     * @param CheckoutOrder $checkoutOrder
     */
    public static function checkoutOrder(MchCheckoutOrder $checkoutOrder){
        return (new EfpsDistributeForm([
            "order_sn"   => $checkoutOrder->order_no,
            "order_type" => "mch_checkout_order",
            "pay_user_id" => $checkoutOrder->pay_user_id
        ]))->save();
    }
}