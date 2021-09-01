<?php

namespace app\plugins\taolijin\forms\mall;

use app\core\ApiCode;
use app\models\BaseModel;
use app\plugins\taolijin\models\TaolijinAli;

class TaoLiJinAliChangeOpenForm extends BaseModel {

    public $id;
    public $is_open;

    public function rules(){
        return [
            [['id', 'is_open'], 'required'],
            [['is_open', 'id'], 'integer']
        ];
    }

    public function save(){
        if(!$this->validate()){
            return $this->responseErrorInfo();
        }

        try {

            $ali = TaolijinAli::findOne($this->id);
            if(!$ali){
                throw new \Exception("账号不存在");
            }

            $ali->is_open = $this->is_open;
            if(!$ali->save()){
                throw new \Exception($this->responseErrorMsg($ali));
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg'  => '保存成功'
            ];
        }catch (\Exception $e){
            return  [
                'code' => ApiCode::CODE_FAIL,
                'msg'  => $e->getMessage()
            ];
        }
    }
}