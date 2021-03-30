<?php
namespace app\forms\efps\distribute;


use app\core\ApiCode;
use app\models\BaseModel;
use app\models\EfpsTransferOrder;
use app\models\Order;

class EfpsDistributeForm extends BaseModel{

    public $order_sn;
    public $order_type;

    public $notifyUrl       = "http://";
    public $amount          = 0;
    public $bankUserName    = "";
    public $bankCardNo      = "";
    public $bankName        = "";
    public $bankAccountType = "";

    public function rules(){
        return [
            [['order_sn', 'order_type'], 'required'],
            [['notifyUrl', 'amount', 'bankUserName', 'bankCardNo', 'bankName', 'bankAccountType'], 'safe']
        ];
    }

    public function save(){
        if(!$this->validate()){
            return $this->responseErrorInfo();
        }
        try {

            $model = EfpsTransferOrder::findOne([
                "order_sn"   => $this->order_sn,
                "order_type" => $this->order_type
            ]);
            if(!$model){
                $model = new EfpsTransferOrder([
                    "order_sn"     => $this->order_sn,
                    "order_type"   => $this->order_type,
                    "amount"       => $this->amount,
                    "created_at"   => time(),
                    "outTradeNo"   => date("YmdHis") . rand(1000, 9999),
                    "customerCode" => \Yii::$app->efps->getCustomerCode(),
                    "status"       => 0
                ]);
            }

            $model->notifyUrl       = $this->notifyUrl;
            $model->bankUserName    = $this->bankUserName;
            $model->bankCardNo      = $this->bankCardNo;
            $model->bankName        = $this->bankName;
            $model->bankAccountType = $this->bankAccountType;
            $model->updated_at      = time();

            if(!$model->save()){
                throw new \Exception($this->responseErrorMsg($model));
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '保存成功'
            ];
        }catch (\Exception $e){
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
            "order_type" => "goods_order",
            "amount"     => $order->total_goods_original_price
        ]))->save();
    }

    /**
     * 结账订单
     * @param CheckoutOrder $checkoutOrder
     */
    public static function checkoutOrder(CheckoutOrder $checkoutOrder){
        return (new EfpsDistributeForm([
            "order_sn"   => $checkoutOrder->order_no,
            "order_type" => "mch_checkout_order",
            "amount"     => $checkoutOrder->order_price
        ]))->save();
    }
}