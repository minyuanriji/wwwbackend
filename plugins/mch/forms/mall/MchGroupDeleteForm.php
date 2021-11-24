<?php

namespace app\plugins\mch\forms\mall;

use app\core\ApiCode;
use app\models\BaseModel;
use app\plugins\mch\models\MchGroup;

class MchGroupDeleteForm extends BaseModel{

    public $id;

    public function rules(){
        return [
            [['id'], 'required']
        ];
    }

    public function delete(){
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }

        try {

            $mchGroup = MchGroup::findOne($this->id);
            if(!$mchGroup){
                throw new \Exception("记录[ID:{$this->id}]不存在");
            }

            $mchGroup->is_delete = 1;
            $mchGroup->deleted_at = time();
            if(!$mchGroup->save()){
                throw new \Exception($this->responseErrorMsg($mchGroup));
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '操作成功'
            ];
        }catch (\Exception $e){
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg'  => $e->getMessage()
            ];
        }

    }
}