<?php

namespace app\forms\api\identity;

use app\core\ApiCode;
use app\models\BaseModel;
use app\models\Order;
use app\models\User;

class IdentityCheckAuthForm extends BaseModel{

    public $user_auth;
    public $order_token;

    public function rules(){
        return [
            [['user_auth','order_token'], 'required']
        ];
    }

    public function check(){
        if(!$this->validate()){
            return $this->responseErrorInfo();
        }

        try {

            //先判单订单
            $query = Order::find()->alias("o")
                ->innerJoin(["u" => User::tableName()], "u.id=o.user_id");
            $exist = $query->andWhere([
                "AND",
                ["o.token" => $this->order_token],
                [">", "o.created_at", time() - 60]
            ])->exists();

            if(!$exist){
                throw new \Exception("授权无效");
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