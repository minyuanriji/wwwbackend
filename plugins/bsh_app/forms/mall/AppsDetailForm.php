<?php
namespace app\plugins\bsh_app\forms\mall;


use app\core\ApiCode;
use app\helpers\ArrayHelper;
use app\models\Apps;
use app\models\BaseModel;

class AppsDetailForm extends BaseModel{

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
            $app = Apps::findOne($this->id);
            if(!$app){
                throw new \Exception("版本记录不存在");
            }
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'data' => ArrayHelper::toArray($app)
            ];
        }catch (\Exception $e){
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg'  => $e->getMessage()
            ];
        }
    }
}