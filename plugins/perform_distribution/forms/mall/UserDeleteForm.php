<?php

namespace app\plugins\perform_distribution\forms\mall;

use app\core\ApiCode;
use app\models\BaseModel;
use app\plugins\perform_distribution\models\PerformDistributionUser;

class UserDeleteForm extends BaseModel{

    public $id;

    public function rules(){
        return [
            [['id'], 'required'],
            [['id'], 'integer']
        ];
    }

    public function save(){

        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }

        try {
            $performDistributionUser = PerformDistributionUser::findOne($this->id);
            if(!$performDistributionUser || $performDistributionUser->is_delete){
                throw new \Exception("数据异常，人员信息不存在");
            }
            $performDistributionUser->is_delete  = 1;
            if(!$performDistributionUser->save()){
                throw new \Exception($this->responseErrorMsg($performDistributionUser));
            }
            return $this->returnApiResultData(ApiCode::CODE_SUCCESS);
        }catch (\Exception $e){
            return $this->returnApiResultData(ApiCode::CODE_FAIL, $e->getMessage());
        }
    }

}