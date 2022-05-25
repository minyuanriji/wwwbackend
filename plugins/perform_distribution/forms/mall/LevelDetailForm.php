<?php

namespace app\plugins\perform_distribution\forms\mall;

use app\core\ApiCode;
use app\models\BaseModel;
use app\plugins\perform_distribution\models\Level;

class LevelDetailForm extends BaseModel{

    public $id;

    public function rules(){
        return [
            [['id'], 'required']
        ];
    }

    public function getDetail(){
        if(!$this->validate()){
            return $this->responseErrorInfo();
        }
        try {

            $level = Level::findOne($this->id);
            if(!$level){
                throw new \Exception("数据异常，等级信息不存在");
            }

            return $this->returnApiResultData(ApiCode::CODE_SUCCESS, null, [
                'detail' => $level->getAttributes()
            ]);
        }catch (\Exception $e){
            return $this->returnApiResultData(ApiCode::CODE_FAIL, $e->getMessage());
        }
    }
}