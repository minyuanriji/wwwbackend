<?php
namespace app\plugins\commission\forms\mall;

use app\core\ApiCode;
use app\models\BaseModel;
use app\plugins\commission\models\CommissionRules;
use MongoDB\Driver\Exception\Exception;

class CommissionRuleDeleteForm  extends BaseModel{

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
            $rule = CommissionRules::findOne($this->id);
            if(!$rule || $rule->is_delete){
                throw new \Exception("无法获取规则记录");
            }

            $rule->is_delete = 1;
            $rule->deleted_at = time();
            if(!$rule->save()){
                throw new \Exception($this->responseErrorMsg($rule));
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