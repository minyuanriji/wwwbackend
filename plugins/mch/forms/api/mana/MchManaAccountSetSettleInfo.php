<?php

namespace app\plugins\mch\forms\api\mana;

use app\core\ApiCode;
use app\models\BaseModel;
use app\models\EfpsMchReviewInfo;
use app\plugins\mch\controllers\api\mana\MchAdminController;

class MchManaAccountSetSettleInfo extends BaseModel {

    public $withdraw_pwd;
    public $paper_settleAccountType;
    public $paper_settleAccountNo;
    public $paper_settleAccount;
    public $paper_settleTarget;
    public $paper_openBank;

    public function rules(){
        return array_merge(parent::rules(), [
            [['withdraw_pwd', 'paper_settleAccountType', 'paper_settleAccountNo',
                'paper_settleAccount', 'paper_settleTarget', 'paper_openBank'], 'required'],
            [['withdraw_pwd'], 'string', 'min' => 6, 'max' => 6]
        ]);
    }

    public function save(){
        if(!$this->validate()){
            return $this->responseErrorInfo();
        }

        try {
            if (!$this->checkBank($this->paper_settleAccountNo))
                throw new \Exception("银行卡填写不正确");

            if(empty(MchAdminController::$adminUser['mch']['withdraw_pwd'])){
                throw new \Exception("交易密码不正确");
            }

            $security = \Yii::$app->getSecurity();
            if(!$security->validatePassword($this->withdraw_pwd, MchAdminController::$adminUser['mch']['withdraw_pwd'])){
                throw new \Exception("交易密码不正确");
            }

            $reviewInfo = EfpsMchReviewInfo::findOne([
                "mch_id" => MchAdminController::$adminUser['mch_id']
            ]);
            if(!$reviewInfo){
                throw new \Exception("无法获取审核信息");
            }

            if(!in_array($this->paper_settleTarget, [1, 2])){
                $this->paper_settleTarget = 2;
            }

            $reviewInfo->paper_settleAccountType = $this->paper_settleAccountType;
            $reviewInfo->paper_settleAccountNo   = $this->paper_settleAccountNo;
            $reviewInfo->paper_settleAccount     = $this->paper_settleAccount;
            $reviewInfo->paper_settleTarget      = $this->paper_settleTarget;
            $reviewInfo->paper_openBank          = $this->paper_openBank;

            if(!$reviewInfo->save()){
                throw new \Exception($this->responseErrorMsg($reviewInfo));
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg'  => "操作成功"
            ];
        }catch (\Exception $e){
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg'  => $e->getMessage(),
            ];
        }
    }

    public function checkBank($no)
    {
        $arr_no = str_split($no);
        $last_n = $arr_no[count($arr_no) - 1];
        krsort($arr_no);
        $i = 1;
        $total = 0;
        foreach ($arr_no as $n) {
            if ($i % 2 == 0) {
                $ix = $n * 2;
                if ($ix >= 10) {
                    $nx = 1 + ($ix % 10);
                    $total += $nx;
                } else {
                    $total += $ix;
                }
            } else {
                $total += $n;
            }
            $i++;
        }
        $total -= $last_n;
        $total *= 9;
        if ($last_n == ($total % 10)) {
            return true;
        } else {
            return false;
        }
    }
}