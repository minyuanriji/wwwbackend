<?php


namespace app\plugins\taolijin\forms\mall;

use app\core\ApiCode;
use app\plugins\sign_in\forms\BaseModel;
use app\plugins\taolijin\models\TaolijinAli;

class TaoLiJinAliDeleteForm extends BaseModel{

    public $id;

    public function rules(){
        return [
            [['id'], 'required'],
        ];
    }

    public function delete(){
        if(!$this->validate()){
            return $this->responseErrorInfo();
        }

        try {

            $ali = TaolijinAli::findOne($this->id);
            if(!$ali){
                throw new \Exception("账号不存在");
            }

            $ali->is_delete = 1;
            if(!$ali->save()){
                throw new \Exception($this->responseErrorMsg($ali));
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg'  => '删除成功'
            ];
        }catch (\Exception $e){
            return  [
                'code' => ApiCode::CODE_FAIL,
                'msg'  => $e->getMessage()
            ];
        }
    }
}