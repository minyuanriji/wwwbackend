<?php
namespace app\plugins\mch\forms\api\mana;


use app\core\ApiCode;
use app\models\BaseModel;
use app\models\EfpsMchReviewInfo;
use app\plugins\mch\controllers\api\mana\MchAdminController;

class MchManaAccountInfoForm extends BaseModel{

    public function get(){
        if(!$this->validate()){
            return $this->responseErrorInfo();
        }

        try {

            //获取易票联结算信息
            $efpsBankData = [
                "paper_settleAccountType" => 0,
                "paper_settleAccountNo"   => "",
                "paper_settleAccount"     => "",
                "paper_settleTarget"      => 0,
                "paper_openBank"          => ""
            ];
            $reviewInfo = EfpsMchReviewInfo::findOne([
                "mch_id" => MchAdminController::$adminUser['mch_id']
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
                    'mobile'        => MchAdminController::$adminUser['mch']['mobile'],
                    'withdraw_fee'  => 0.5,
                    'account_money' => (float)MchAdminController::$adminUser['mch']['account_money'],
                    'is_pwd_set'    => !empty(MchAdminController::$adminUser['mch']['withdraw_pwd']) ? 1 : 0,
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