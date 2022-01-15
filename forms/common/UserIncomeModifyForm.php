<?php

namespace app\forms\common;

use app\models\BaseModel;
use app\models\IncomeLog;
use app\models\User;

class UserIncomeModifyForm extends BaseModel{

    public $price;
    public $type; //类型：1=收入，2=支出
    public $flag; //0冻结，1结算
    public $user_id;
    public $source_id;
    public $source_type;
    public $desc;
    public $is_manual;

    public function rules(){
        return [
            [['type', 'price', 'flag', 'user_id', 'source_id', 'source_type', 'desc'], 'required'],
            [['is_manual'], 'safe']
        ];
    }

    public function modify(User $user, $trans = true){

        $this->user_id = $user->id;

        if(!$this->validate()){
            throw new \Exception($this->responseErrorMsg());
        }

        $t = $trans ? \Yii::$app->db->beginTransaction() : null;

        try {
            $totalIncome = floatval($user->income + $user->income_frozen);

            if($this->type == 1){ //收入
                $user->total_income = $totalIncome + $this->price;
                if($this->flag == 0){ //冻结
                    $user->income_frozen = floatval($user->income_frozen) + $this->price;
                }else{ //结算
                    $user->income = floatval($user->income) + $this->price;
                }
            }else{ //支出
                $user->total_income = max(0, $totalIncome - floatval($this->price));
                if($this->flag == 0){ //冻结
                    $user->income_frozen = max(0, floatval($user->income_frozen) - floatval($this->price));
                }else{ //结算
                    $user->income = max(0, floatval($user->income) - floatval($this->price));
                }
            }

            if(!$user->save()){
                throw new \Exception(json_encode($user->getErrors()));
            }
            $incomeLog = new IncomeLog([
                "mall_id"     => $user->mall_id,
                "user_id"     => $user->id,
                "type"        => $this->type,
                "money"       => $totalIncome,
                "income"      => floatval($this->price),
                "desc"        => $this->desc,
                "flag"        => $this->flag,
                "source_id"   => $this->source_id,
                "source_type" => $this->source_type,
                "created_at"  => time(),
                "updated_at"  => time(),
                "is_manual"   => $this->is_manual
            ]);
            if(!$incomeLog->save()){
                throw new \Exception(json_encode($incomeLog->getErrors()));
            }

            $trans && $t->commit();
        }catch (\Exception $e){
            $trans && $t->rollBack();
            throw $e;
        }
    }

}