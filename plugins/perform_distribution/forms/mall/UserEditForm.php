<?php

namespace app\plugins\perform_distribution\forms\mall;

use app\core\ApiCode;
use app\models\BaseModel;
use app\models\User;
use app\plugins\perform_distribution\models\PerformDistributionUser;

class UserEditForm extends BaseModel{

    public $id;
    public $user_id;
    public $level_id;

    public function rules(){
        return [
            [['user_id', 'level_id'], 'required'],
            [['user_id', 'id', 'level_id'], 'integer']
        ];
    }

    public function save(){
        if(!$this->validate()){
            return $this->responseErrorInfo();
        }
        try {

            $user = User::findOne($this->user_id);
            if(!$user || $user->is_delete){
                throw new \Exception("用户不存在");
            }

            $performDistributionUser = PerformDistributionUser::findOne(["user_id" => $this->user_id]);
            if(!$performDistributionUser){
                $performDistributionUser = new PerformDistributionUser([
                    "mall_id"    => $user->mall_id,
                    "user_id"    => $user->id,
                    "created_at" => time()
                ]);
            }
            $performDistributionUser->level_id   = $this->level_id;
            $performDistributionUser->updated_at = time();
            $performDistributionUser->is_delete  = 0;
            if(!$performDistributionUser->save()){
                throw new \Exception($this->responseErrorMsg($performDistributionUser));
            }

            return $this->returnApiResultData(ApiCode::CODE_SUCCESS, null);
        }catch (\Exception $e){
            return $this->returnApiResultData(ApiCode::CODE_FAIL, $e->getMessage());
        }
    }

}