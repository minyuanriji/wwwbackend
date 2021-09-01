<?php


namespace app\forms\common;


use app\models\BaseModel;
use app\models\ScoreLog;
use app\models\User;

class UserScoreModifyForm extends BaseModel{

    public $type; //类型：1=收入，2=支出
    public $score; //变动积分
    public $desc; //变动说明
    public $custom_desc; //自定义详细说明|记录
    public $source_type;

    public function rules(){
        return [
            [['type', 'score', 'desc', 'source_type'], 'required'],
            [['custom_desc'], 'safe']
        ];
    }

    public function modify(User $user){
        if(!$this->validate()){
            throw new \Exception($this->responseErrorMsg());
        }

        $currentScore = floatval($user->static_score);
        if($this->type == 1){ //增加
            $user->static_score = $currentScore + intval($this->score);
        }else{ //减少
            $user->static_score = max(0, $currentScore - floatval($this->score));
        }
        if(!$user->save()){
            throw new \Exception(json_encode($user->getErrors()));
        }

        //生成交易记录
        $log = new ScoreLog([
            "mall_id"       => $user->mall_id,
            "user_id"       => $user->id,
            "type"          => $this->type,
            "score"         => floatval($this->score),
            "current_score" => $currentScore,
            "desc"          => $this->desc,
            "custom_desc"   => $this->custom_desc,
            "created_at"    => time(),
            "source_type"   => $this->source_type
        ]);
        if (!$log->save()) {
            throw new \Exception(json_encode($log->getErrors()));
        }
    }
}