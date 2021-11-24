<?php

namespace app\plugins\mch\forms\mall;

use app\core\ApiCode;
use app\models\BaseModel;
use app\plugins\mch\models\MchGroup;

class MchGroupDetailForm extends BaseModel{

    public $id;

    public function rules(){
        return array_merge(parent::rules(), [
            [['id'], 'required']
        ]);
    }

    public function getDetail(){

        if(!$this->validate()){
            return $this->responseErrorInfo();
        }

        try {

            $mchGroup = MchGroup::find()->with(["mch", "store"])->where([
                "id"        => $this->id,
                "is_delete" => 0
            ])->asArray()->one();
            if(!$mchGroup){
                throw new \Exception("连锁总店[ID:{$this->id}]记录信息不存在");
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'data' => [
                    'detail' => $mchGroup
                ]
            ];
        }catch (\Exception $e){
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg'  => $e->getMessage()
            ];
        }
    }
}