<?php
/**
 * 商家结账单自动结算
 */
namespace app\mch\forms\order;


use app\helpers\SerializeHelper;
use app\models\BaseModel;
use app\models\Cash;
use app\models\CashLog;
use app\models\IncomeLog;

class CheckoutOrderAutoSettleForm extends BaseModel{

    public $user_id; //商家关联的用户ID

    public $name;           //收款人
    public $bank_name;      //银行
    public $bank_account;   //银行卡号
    public $wechat_qrcode;  //微信收款码

    public $content = "商家结账单结算";

    public function rules(){
        return [
            [['user_id'], 'required'],
            [['user_id'], 'integer'],
            [['content'], 'string']
        ];
    }

    public function save(){

    }


    private function setCashLog(){

        $extra = SerializeHelper::encode([
            'name'          => $this->name,
            'mobile'        => $this->mobile,
            'bank_name'     => $this->bank_name,
            'bank_account'  => $this->bank_account,
            'wechat_qrcode' => $this->wechat_qrcode
        ]);

        $content = SerializeHelper::encode(['user_content' => $this->content]);
        $cash = new Cash();
        $cash->mall_id = \Yii::$app->mall->id;
        $cash->user_id = $this->user_id;
        $cash->price = $this->price;
        $cash->fact_price = $this->price;
        if ($payment['cash_service_fee'] > 0 && $payment['cash_service_fee'] < 100) {
            $cash->fact_price = (100 - $payment['cash_service_fee']) * $this->price / 100;
        }
        $cash->order_no = $this->getCashOrder();
        $cash->service_fee_rate = $payment['cash_service_fee'];
        $cash->content = $content;
        $cash->type = $this->type;
        $cash->extra = $extra;
        if (!$cash->save()) {
            return $this->returnApiResultData();
        }
        \Yii::$app->currency->setUser($user)->income->sub(floatval($cash->price), "由用户（{$user->id}）申请提现（{$cash->order_no}）", 0, IncomeLog::FLAG_CASH);
        $log = new CashLog();
        $log->price = $this->price;
        $log->type = 2;
        $log->desc = '提现申请';
        $log->user_id = $user->id;
        $log->mall_id = $user->mall_id;
        $log->save();
    }
}