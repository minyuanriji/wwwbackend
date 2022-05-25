<?php

namespace app\plugins\perform_distribution\forms\mall;

use app\core\ApiCode;
use app\models\BaseModel;
use app\plugins\perform_distribution\models\Level;

class LevelEditForm extends BaseModel{

    public $id;
    public $name;
    public $level;

    public function rules() {
        return [
            [['name', 'level'], 'required'],
            [['name'], 'trim'],
            [['id', 'level'], 'integer']
        ];
    }

    public function save(){
        if(!$this->validate()){
            return $this->responseErrorInfo();
        }
        try {
            if($this->id){
                $level = Level::findOne($this->id);
                if(!$level){
                    throw new \Exception("数据异常，等级信息不存在");
                }
            }else{
                $level = new Level([
                    "mall_id"    => \Yii::$app->mall->id,
                    "created_at" => time()
                ]);
            }
            $level->level      = $this->level;
            $level->name       = $this->name;
            $level->updated_at = time();
            $level->is_delete  = 0;
            if(!$level->save()){
                throw new \Exception($this->responseErrorMsg($level));
            }
            return $this->returnApiResultData(ApiCode::CODE_SUCCESS);
        }catch (\Exception $e){
            return $this->returnApiResultData(ApiCode::CODE_FAIL, $e->getMessage());
        }
    }
}