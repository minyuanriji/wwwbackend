<?php

namespace app\plugins\smart_shop\forms\common;

use app\models\BaseModel;
use app\plugins\smart_shop\models\StoreAccount;
use app\plugins\smart_shop\models\StoreAccountLog;

class StoreAccountBalanceModifyForm extends BaseModel{

    public $source_type;
    public $source_id;
    public $balance;
    public $desc;

    private $type;

    public function rules(){
        return [
            [['source_type', 'source_id', 'balance', 'desc'], 'required'],
            [['balance'], 'number']
        ];
    }

    /**
     * 增加
     * @param StoreAccount $account
     * @param $is_trans 是否开启事务
     * @throws \Exception
     */
    public function add(StoreAccount $account, $is_trans = false){
        $this->type = 1;
        $this->modify($account, $is_trans);
    }

    /**
     * 减少
     * @param StoreAccount $account
     * @param $is_trans 是否开启事务
     * @throws \Exception
     */
    public function sub(StoreAccount $account, $is_trans = false){
        $this->type = 2;
        $this->modify($account, $is_trans);
    }

    /**
     * 修改门店账户余额
     * @param StoreAccount $account
     * @param $is_trans 是否开启事务
     * @throws \Exception
     */
    private function modify(StoreAccount $account, $is_trans){
        $is_trans && ($t = \Yii::$app->db->beginTransaction());
        if(!$this->validate()){
            throw new \Exception($this->responseErrorMsg());
        }

        try {
            $beforeBalance = $account->balance;

            if($this->type == 1){ //增加
                $account->balance += (float)$this->balance;
            }else{ //减少
                $account->balance = max(0, $account->balance - (float)$this->balance);
            }
            $account->updated_at = time();
            if(!$account->save()){
                throw new \Exception($this->responseErrorMsg($account));
            }

            $log = new StoreAccountLog([
                "mall_id"     => $account->mall_id,
                "ss_mch_id"   => $account->ss_mch_id,
                "ss_store_id" => $account->ss_store_id,
                "created_at"  => time(),
                "source_type" => $this->source_type,
                "source_id"   => $this->source_id,
                "type"        => $this->type,
                "before_num"  => $beforeBalance,
                "num"         => $this->balance,
                "desc"        => $this->desc
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