<?php

namespace app\plugins\integral_card\forms\mall;

use app\core\ApiCode;
use app\models\BaseModel;
use app\plugins\integral_card\models\ScoreFromFree;

class FromFreeDeleteForm extends BaseModel{

    public $id;

    public function rules(){
        return [
            [['id'], 'required'],
            [['id'], 'integer']
        ];
    }

    public function delete(){

        if(!$this->validate()){
            return $this->responseErrorInfo();
        }

        try {

            $fromFree = ScoreFromFree::findOne($this->id);
            if(!$fromFree){
                throw new \Exception("数据不存在");
            }

            $fromFree->is_delete = 1;
            $fromFree->deleted_at = time();
            if(!$fromFree->save()){
                throw new \Exception($this->responseErrorMsg($fromFree));
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg'  => '删除成功'
            ];
        }catch (\Exception $e){
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg'  => $e->getMessage()
            ];
        }
    }

}