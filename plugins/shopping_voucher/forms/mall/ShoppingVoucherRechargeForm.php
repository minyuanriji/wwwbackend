<?php

namespace app\plugins\shopping_voucher\forms\mall;

use app\core\ApiCode;
use app\models\BaseModel;
use app\models\User;
use app\plugins\shopping_voucher\forms\common\ShoppingVoucherLogModifiyForm;

class ShoppingVoucherRechargeForm extends BaseModel{

    public $type;
    public $user_id;
    public $number;
    public $remark;
    public $is_manual;

    public function rules(){
        return [
            [['type', 'user_id', 'number'], 'required'],
            [['remark','is_manual'], 'safe']
        ];
    }

    public function recharge(){
        if(!$this->validate()){
            return $this->responseErrorInfo();
        }

        try {

            $user = User::findOne((int)$this->user_id);
            if(!$user || $user->is_delete){
                throw new \Exception("用户不存在");
            }

            if($this->number <= 0){
                throw new \Exception("数量不能小于0");
            }

            $adminId = \Yii::$app->admin->id;

            $modifyForm = new ShoppingVoucherLogModifiyForm([
                "money"       => $this->number,
                "desc"        => "管理员ID:{$adminId}操作。". $this->remark,
                "source_id"   => $adminId . ":" . time(),
                "source_type" => 'admin'
            ]);

            if($this->type == 1){ //充值
                $modifyForm->add($user, true);
            }else{ //扣减
                $modifyForm->sub($user, true);
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg'  => '操作成功'
            ];
        }catch (\Exception $e){
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg'  => $e->getMessage()
            ];
        }
    }
}