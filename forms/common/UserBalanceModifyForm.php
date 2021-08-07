<?php


namespace app\forms\common;


use app\models\BalanceLog;
use app\models\BaseModel;
use app\models\User;

class UserBalanceModifyForm extends BaseModel{

    public $type;
    public $money;
    public $custom_desc;
    public $source_id;
    public $source_type;
    public $desc;

    public function rules(){
        return [
            [['type', 'money', 'source_id', 'source_type', 'desc'], 'required'],
            [['custom_desc'], 'string']
        ];
    }

    public function modify(User $user){
        if(!$this->validate()){
            throw new \Exception($this->responseErrorMsg());
        }

        $currentBalance = floatval($user->balance);
        if($this->type == BalanceLog::TYPE_ADD){ //增加
            $user->balance = $currentBalance + floatval($this->money);
        }else{ //减少
            $user->balance = max(0, $currentBalance - floatval($this->money));
        }
        if(!$user->save()){
            throw new \Exception(json_encode($user->getErrors()));
        }

        //生成交易记录
        $log = new BalanceLog([
            "mall_id"     => $user->mall_id,
            "user_id"     => $user->id,
            "balance"     => $user->balance,
            "type"        => $this->type,
            "money"       => floatval($this->money),
            "custom_desc" => $this->custom_desc,
            "desc"        => $this->desc,
            "source_type" => $this->source_type,
            "source_id"   => $this->source_id
        ]);
        if (!$log->save()) {
            throw new \Exception(json_encode($log->getErrors()));
        }
    }
}