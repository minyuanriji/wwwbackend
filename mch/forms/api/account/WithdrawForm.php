<?php
namespace app\mch\forms\api\account;

use app\core\ApiCode;
use app\mch\forms\mch\MchAccountWithdraw;
use app\models\BaseModel;
use app\plugins\mch\models\Mch;

class WithdrawForm extends BaseModel{

    public $mch_id;
    public $withdraw_pwd;
    public $money;

    public function rules(){
        return array_merge(parent::rules(), [
            [['mch_id', 'withdraw_pwd', 'money'], 'required'],
            [['mch_id'], 'integer'],
            [['withdraw_pwd'], 'string', 'min' => 6, 'max' => 6],
            [['money'], 'number', 'min' => 0]
        ]);
    }

    public function save(){
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

            if(empty($mch->withdraw_pwd)){
                throw new \Exception("提现密码不正确");
            }

            $security = \Yii::$app->getSecurity();
            if(!$security->validatePassword($this->withdraw_pwd, $mch->withdraw_pwd)){
                throw new \Exception("提现密码不正确");
            }

            //设置一个提现队列
            $res = MchAccountWithdraw::efpsBank($mch, $this->money, "系统自动提现操作");

            return $res;
        }catch (\Exception $e){
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg'  => $e->getMessage(),
            ];
        }
    }
}