<?php
namespace app\mch\forms\api\apply;

use app\core\ApiCode;
use app\mch\forms\mch\EfpsReviewInfoForm;
use app\models\EfpsMchReviewInfo;
use app\plugins\mch\models\Mch;

class SettleInfoForm extends EfpsReviewInfoForm{

    public function save(){
        try {

            $this->checkData();

            $res = parent::save();

            return $res;
        }catch (\Exception $e){
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg' => $e->getMessage(),
                'error' => [
                    'line' => $e->getLine()
                ]
            ];
        }
    }

    private function checkData(){
        $settleAccountType = (int)$this->paper_settleAccountType;
        if(!in_array($settleAccountType, [
            EfpsMchReviewInfo::SETTLEACCOUNTTYPE_ENT,
            EfpsMchReviewInfo::SETTLEACCOUNTTYPE_PER,
            EfpsMchReviewInfo::SETTLEACCOUNTTYPE_AU_ENT,
            EfpsMchReviewInfo::SETTLEACCOUNTTYPE_AU_PER])){
            throw new \Exception("请设置结算账户类型");
        }

        if(empty($this->paper_settleAccountNo)){ //结算账户号
            throw new \Exception("请设置结算账户号");
        }

        if(empty($this->paper_settleAccount)){ //结算账户名
            throw new \Exception("请设置结算账户名");
        }

        if(empty($this->paper_openBank)){ //开户银行
            throw new \Exception("请设置开户银行");
        }

        if($settleAccountType ==  EfpsMchReviewInfo::SETTLEACCOUNTTYPE_ENT){
            if(empty($this->paper_openSubBank)){ //开户支行
                throw new \Exception("请设置开户支行");
            }
            if(empty($this->paper_openBankCode)){ //开户行联行号
                //throw new \Exception("请设置开户行联行号");
            }
        }

        if(in_array($settleAccountType, [EfpsMchReviewInfo::SETTLEACCOUNTTYPE_AU_ENT,
                EfpsMchReviewInfo::SETTLEACCOUNTTYPE_AU_PER]) && empty($this->paper_settleAttachment)){
            //throw new \Exception("请设置结算账户附件");
        }


    }
}