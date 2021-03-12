<?php
/**
 * 商家结账单自动结算
 */
namespace app\mch\forms\order;

use app\core\ApiCode;
use app\models\BaseModel;
use app\plugins\mch\forms\mall\CashEditForm;
use app\plugins\mch\models\MchAccountLog;
use app\plugins\mch\models\MchCash;

class CheckoutOrderAutoSettleForm extends BaseModel{

    public $price;                   //金额
    public $mch_id;                  //商家ID

    public $order_id;


    public function rules(){
        return [
            [['mch_id', 'price', 'order_id'], 'required'],
            [['mch_id', 'order_id'], 'integer'],
            [['price'], 'number'],
            [[], 'string']
        ];
    }

    public function save(){

        if(!$this->validate()){
            throw new \Exception($this->responseErrorMsg());
        }

        $t = \Yii::$app->db->beginTransaction();
        try {
            $cashId = $this->setCashLog();

            //自动打款
            $cashEditForm = new CashEditForm([
                "id"            => $cashId,
                "transfer_type" => 1
            ]);
            $res = $cashEditForm->transfer();
            if($res['code'] != ApiCode::CODE_SUCCESS){
                throw new \Exception($res['msg']);
            }
            print_r($res);
            exit;
            //$t->commit();
        }catch (\Exception $e){
            $t->rollBack();
            throw new \Exception($e->getMessage());
        }


    }

    /**
     * 设置提现记录
     * @param $payment
     * @return array
     */
    private function setCashLog(){

        $mchCash = new MchCash();
        $mchCash->mall_id         = \Yii::$app->mall->id;
        $mchCash->mch_id          = $this->mch_id;
        $mchCash->money           = $this->price;
        $mchCash->order_no        = $this->getCashOrder();
        $mchCash->status          = 1; //同意打款
        $mchCash->transfer_status = 0;
        $mchCash->type            = "auto"; //微信自动打款
        $mchCash->virtual_type    = 0;
        $mchCash->type_data       = "{}";
        $mchCash->created_at      = time();
        $mchCash->updated_at      = time();
        $mchCash->content         = "结账单打款(".$this->order_id.")";

        if (!$mchCash->save()) {
            throw new \Exception($this->responseErrorMsg($mchCash));
        }

        $accountLog = new MchAccountLog();
        $accountLog->mall_id    = \Yii::$app->mall->id;
        $accountLog->mch_id     = $this->mch_id;
        $accountLog->money      = $this->price;
        $accountLog->desc       = "结账单打款(".$this->order_id.")";
        $accountLog->type       = 2; //支出
        $accountLog->created_at = time();
        if (!$accountLog->save()) {
            throw new \Exception($this->responseErrorMsg($accountLog));
        }

        return $mchCash->id;
    }

    private function getCashOrder(){
        $order_no = null;
        while (true) {
            $order_no = 'MTX' . date('YmdHis') . rand(10000, 99999);
            $exist = MchCash::find()->where(['mall_id' => \Yii::$app->mall->id, 'order_no' => $order_no])->exists();
            if (!$exist) {
                break;
            }
        }
        return $order_no;
    }
}