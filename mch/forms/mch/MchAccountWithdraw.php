<?php
namespace app\mch\forms\mch;


use app\core\ApiCode;
use app\models\BaseModel;
use app\models\EfpsMchReviewInfo;
use app\plugins\mch\models\Mch;
use app\plugins\mch\models\MchCash;

class MchAccountWithdraw extends BaseModel{

    public $mch_id;
    public $money;
    public $type;
    public $content;

    public function rules(){
        return [
            [['mch_id', 'money', 'type', 'content'], 'required'],
            [["money"], "number", "min" => 0],
            [["mch_id"], "integer"]
        ];
    }

    public function save(){

        if(!$this->validate()){
            return $this->responseErrorInfo();
        }

        $t = \Yii::$app->getDb()->beginTransaction();
        try {

            $mch = Mch::findOne($this->mch_id);
            if(!$mch || $mch->review_status != Mch::REVIEW_STATUS_CHECKED || $mch->is_delete){
                throw new \Exception("商户不存在");
            }

            $factPrice = $this->money;
            $this->money += 0.5;

            if($mch->account_money < $this->money){
                throw new \Exception("商户帐户余额不足");
            }

            if($factPrice <= 0){
                throw new \Exception("提现金额必须大于0");
            }

            $res = MchAccountModifyForm::modify($mch, $this->money, $this->content, false);
            if($res['code'] != ApiCode::CODE_SUCCESS){
                throw new \Exception($res['msg']);
            }

            $mchCash = new MchCash([
                "mall_id"         => $mch->mall_id,
                "mch_id"          => $mch->id,
                "money"           => $this->money,
                "fact_price"      => $factPrice,
                "order_no"        => "MC" . date("YmdHis") . rand(1000, 9999),
                "status"          => 0,
                "transfer_status" => 0,
                "type"            => $this->type,
                "virtual_type"    => 0,
                "created_at"      => time(),
                "updated_at"      => time(),
                "content"         => $this->content,
            ]);

            $typeData = [];

            //通过易票联提现打款到银行卡
            if($mchCash->type == "efps_bank"){

                $mchCash->status = 1;

                $reviewInfo = EfpsMchReviewInfo::findOne([
                    "mch_id" => $mch->id
                ]);
                if(!$reviewInfo || empty($reviewInfo->paper_settleAccount) ||
                     empty($reviewInfo->paper_settleAccountNo) ||
                     empty($reviewInfo->paper_openBank)){
                    throw new \Exception("未设置结算信息");
                }
                $typeData['bankUserName']    = $reviewInfo->paper_settleAccount;
                $typeData['bankCardNo']      = $reviewInfo->paper_settleAccountNo;
                $typeData['bankName']        = $reviewInfo->paper_openBank;
                $typeData['bankAccountType'] = "2";
            }

            $mchCash->type_data = json_encode($typeData);
            if(!$mchCash->save()){
                throw new \Exception($this->responseErrorMsg($mchCash));
            }

            $t->commit();

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg'  => "操作成功",
                'data' => [
                    'account_money' => ($mch->account_money - $this->money)
                ]
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
     * 通过易票联提现打款到银行卡
     * @param Mch $mch
     * @param $money
     * @param $content
     * @return array
     */
    public static function efpsBank(Mch $mch, $money, $content){
        return (new static([
            "mch_id"  => $mch->id,
            "money"   => $money,
            "type"    => "efps_bank",
            "content" => $content
        ]))->save();
    }

}