<?php
namespace app\mch\forms\api\account;


use app\core\ApiCode;
use app\models\BaseModel;
use app\models\EfpsMchReviewInfo;
use app\plugins\mch\models\Mch;

class InfoForm extends BaseModel{

    public $mch_id;

    public function rules(){
        return array_merge(parent::rules(), [
            [['mch_id'], 'required']
        ]);
    }

    public function get(){
        if(!$this->validate()){
            return $this->responseErrorInfo();
        }

        try {
            $mch = Mch::findOne([
                "id"            => $this->mch_id,
                "review_status" => Mch::REVIEW_STATUS_CHECKED
            ]);
            if(!$mch){
                throw new \Exception("商户不存在");
            }

            //获取易票联结算信息
            $efpsBankData = [
                "paper_settleAccountType" => 0,
                "paper_settleAccountNo"   => "",
                "paper_settleAccount"     => "",
                "paper_settleTarget"      => 0,
                "paper_openBank"          => ""
            ];
            $reviewInfo = EfpsMchReviewInfo::findOne([
                "mch_id" => $this->mch_id
            ]);
            if($reviewInfo){
                $efpsBankData['paper_settleAccountType'] = $reviewInfo->paper_settleAccountType;
                $efpsBankData['paper_settleAccountNo']   = $reviewInfo->paper_settleAccountNo;
                $efpsBankData['paper_settleAccount']     = $reviewInfo->paper_settleAccount;
                $efpsBankData['paper_settleTarget']      = $reviewInfo->paper_settleTarget;
                $efpsBankData['paper_openBank']          = $reviewInfo->paper_openBank;
            }

            if(empty($efpsBankData['paper_settleAccountType'])){
                $efpsBankData['paper_settleAccountType'] = 0;
            }

            if(empty($efpsBankData['paper_settleTarget'])){
                $efpsBankData['paper_settleTarget'] = 2;
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg'  => "操作成功",
                'data' => [
                    'mobile'        => $mch->mobile,
                    'account_money' => (float)$mch->account_money,
                    'is_pwd_set'    => !empty($mch->withdraw_pwd) ? 1 : 0,
                    'efps_bank'     => $efpsBankData
                ]
            ];
        }catch (\Exception $e){
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg'  => $e->getMessage(),
            ];
        }
    }

}