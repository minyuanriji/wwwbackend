<?php
namespace app\mch\forms\order;

use app\core\ApiCode;
use app\models\BaseModel;
use app\plugins\mch\forms\mall\CashEditForm;
use app\plugins\mch\forms\mall\SettingForm;
use app\plugins\mch\models\Mch;
use app\plugins\mch\models\MchAccountLog;
use app\plugins\mch\models\MchCash;

class MchAutoSettleForm extends BaseModel{

    public $price;   //结算金额
    public $mch_id;  //商家ID
    public $desc;    //描述

    public static $errorMsg;

    public function rules(){
        return [
            [['mch_id', 'price'], 'required'],
            [['mch_id'], 'integer'],
            [['price'], 'number'],
            [['desc'], 'string']
        ];
    }

    /**
     * 商家自动结算
     * @return false
     * @throws \Exception
     */
    public function save(){

        if(!$this->validate()){
            static::$errorMsg = $this->responseErrorMsg();
            return false;
        }

        $t = \Yii::$app->db->beginTransaction();
        try {

            $mch = Mch::findOne([
                "id"            => $this->mch_id,
                "review_status" => Mch::REVIEW_STATUS_CHECKED,
                "is_delete"     => 0
            ]);
            if(!$mch){
                throw new \Exception("商户不存在");
            }

            $mchCash = $this->setCashLog($mch);

            //自动打款
            $cashEditForm = new CashEditForm([
                "id"            => $mchCash->id,
                "transfer_type" => 1
            ]);
            $res = $cashEditForm->transfer();
            if($res['code'] != ApiCode::CODE_SUCCESS){
                //打款失败，试试转到余额
                $mchCash->type = "balance";
                $mchCash->content .= "。提现失败，自动转入余额";
                if(!$mchCash->save()){
                    throw new \Exception($mchCash->responseErrorMsg());
                }
                $res = $cashEditForm->transfer();
                if($res['code'] != ApiCode::CODE_SUCCESS){
                    throw new \Exception($res['msg']);
                }
            }
            $t->commit();
        }catch (\Exception $e){
            $t->rollBack();
            static::$errorMsg = $e->getMessage();
            return false;
        }

        return true;
    }

    /**
     * 设置提现记录
     * @param $payment
     * @return array
     */
    private function setCashLog(Mch $mch){

        $serviceFeeRate = max(0, min(100, (int)$mch->transfer_rate));
        $serviceFee = ($serviceFeeRate/100) * floatval($this->price);

        $mchCash = new MchCash();
        $mchCash->mall_id          = \Yii::$app->mall->id;
        $mchCash->mch_id           = $this->mch_id;
        $mchCash->money            = floatval($this->price);
        $mchCash->order_no         = $this->getCashOrder();
        $mchCash->status           = 1; //同意打款
        $mchCash->transfer_status  = 0;
        $mchCash->type             = "auto"; //微信自动打款
        $mchCash->virtual_type     = 0;
        $mchCash->type_data        = "{}";
        $mchCash->created_at       = time();
        $mchCash->updated_at       = time();
        $mchCash->content          = $this->desc . "提现到微信";
        $mchCash->service_fee_rate = $serviceFeeRate;
        $mchCash->fact_price       = $mchCash->money - $serviceFee;

        if (!$mchCash->save()) {
            throw new \Exception($this->responseErrorMsg($mchCash));
        }

        return $mchCash;
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