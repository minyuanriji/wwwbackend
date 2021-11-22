<?php
namespace app\plugins\bsh_app\forms\mall;


use app\core\ApiCode;
use app\models\Apps;
use app\models\BaseModel;

class AppsDeleteForm extends BaseModel{

    public $id;

    public function rules(){
        return [
            [['id'], 'required']
        ];
    }

    public function delete(){
        if(!$this->validate()){
            return $this->responseErrorInfo();
        }

        try {
            $app = Apps::findOne($this->id);
            if(!$app){
                throw new \Exception("版本记录不存在");
            }

            $app->delete();

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