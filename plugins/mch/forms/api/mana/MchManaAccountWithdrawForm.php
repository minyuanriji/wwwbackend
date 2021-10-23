<?php
namespace app\plugins\mch\forms\api\mana;

use app\core\ApiCode;
use app\mch\forms\mch\MchAccountWithdraw;
use app\models\BaseModel;
use app\plugins\mch\controllers\api\mana\MchAdminController;
use app\plugins\mch\models\Mch;

class MchManaAccountWithdrawForm extends BaseModel{

    public $withdraw_pwd;
    public $money;

    public function rules(){
        return array_merge(parent::rules(), [
            [['withdraw_pwd', 'money'], 'required'],
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
                "id"            => MchAdminController::$adminUser['mch_id'],
                "is_delete"     => 0,
                "review_status" => Mch::REVIEW_STATUS_CHECKED
            ]);
            if(!$mch){
                throw new \Exception("商户不存在");
            }

            $security = \Yii::$app->getSecurity();
            if(empty($mch->withdraw_pwd) || !$security->validatePassword($this->withdraw_pwd, $mch->withdraw_pwd)){
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