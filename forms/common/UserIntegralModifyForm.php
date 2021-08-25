<?php


namespace app\forms\common;


use app\models\BaseModel;
use app\models\IntegralLog;
use app\models\User;

class UserIntegralModifyForm extends BaseModel{

    public $type; //类型：1=收入，2=支出
    public $integral; //变动红包
    public $desc; //变动说明
    public $source_id;
    public $source_type;
    public $is_manual = 0; //是否是后台管理员充值 默认、0   0、否 1、是

    public function rules(){
        return [
            [['type', 'integral', 'desc', 'source_id', 'source_type'], 'required'],
            [['is_manual'], 'safe']
        ];
    }

    public function modify(User $user){
        if(!$this->validate()){
            throw new \Exception($this->responseErrorMsg());
        }

        $currentIntegral = floatval($user->static_integral);
        if($this->type == 1){ //增加
            $user->static_integral = $currentIntegral + floatval($this->integral);
        }else{ //减少
            $user->static_integral = max(0, $currentIntegral - floatval($this->integral));
        }
        if(!$user->save()){
            throw new \Exception(json_encode($user->getErrors()));
        }

        //生成交易记录
        $log = new IntegralLog([
            "mall_id"          => $user->mall_id,
            "user_id"          => $user->id,
            "type"             => $this->type,
            "integral"         => floatval($this->integral),
            "current_integral" => $currentIntegral,
            "desc"             => $this->desc,
            "created_at"       => time(),
            "source_type"      => $this->source_type,
            "source_id"        => $this->source_id,
            "is_manual"        => (int)$this->is_manual
        ]);
        if (!$log->save()) {
            throw new \Exception(json_encode($log->getErrors()));
        }
    }
}