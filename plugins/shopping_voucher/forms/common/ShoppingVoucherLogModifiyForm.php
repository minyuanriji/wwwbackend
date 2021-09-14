<?php

namespace app\plugins\shopping_voucher\forms\common;

use app\models\BaseModel;
use app\models\User;
use app\plugins\shopping_voucher\models\ShoppingVoucherLog;
use app\plugins\shopping_voucher\models\ShoppingVoucherUser;

class ShoppingVoucherLogModifiyForm extends BaseModel{

    public $money;
    public $desc;
    public $source_id;
    public $source_type;
    public $type;

    public function rules(){
        return [
            [['money', 'desc', 'source_id', 'source_type'], 'required'],
            [['money'], 'number', 'min' => 0],
            [['type'], 'integer']
        ];
    }

    /**
     * 增加购物券
     * @param ShoppingVoucherUser|User $user
     * @param false $is_trans 是否开启事务
     * @throws \Exception
     */
    public function add($user, $is_trans = false){
        $user = ($user instanceof User) ? $user->shoppingVoucherUser : $user;
        $this->type = 1;
        $this->modify($user, $is_trans);
    }

    /**
     * 扣减购物券
     * @param ShoppingVoucherUser|User $user
     * @param false $is_trans 是否开启事务
     * @throws \Exception
     */
    public function sub($user, $is_trans = false){
        $user = ($user instanceof User) ? $user->shoppingVoucherUser : $user;
        $this->type = 2;
        $this->modify($user, $is_trans);
    }

    /**
     * 修改购物券
     * @param ShoppingVoucherUser $user
     * @param false $is_trans 是否开启事务
     * @throws \Exception
     */
    public function modify(ShoppingVoucherUser $user, $is_trans = false){

        $is_trans && ($t = \Yii::$app->db->beginTransaction());

        try {
            if(!$this->validate()){
                throw new \Exception($this->responseErrorMsg());
            }

            $currentMoney = $user->money;

            if($this->type == 1){ //增加
                $user->money += (float)$this->money;
            }else{ //减少
                $user->money = max(0, $user->money - (float)$this->money);
            }
            $user->updated_at = time();
            if(!$user->save()){
                throw new \Exception($this->responseErrorMsg($user));
            }

            $log = new ShoppingVoucherLog([
                "mall_id"       => $user->mall_id,
                "user_id"       => $user->user_id,
                "type"          => $this->type,
                "current_money" => $currentMoney,
                "money"         => (float)$this->money,
                "desc"          => $this->desc,
                "source_id"     => $this->source_id,
                "source_type"   => $this->source_type,
                "created_at"    => time(),
                "updated_at"    => time()
            ]);
            if(!$log->save()){
                throw new \Exception($this->responseErrorMsg($log));
            }

            $is_trans && ($t->commit());

        }catch (\Exception $e){
            $is_trans && ($t->rollBack());
            throw $e;
        }

    }
}