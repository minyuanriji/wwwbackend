<?php
namespace app\plugins\commission\forms\mall;

use app\core\ApiCode;
use app\models\BaseModel;
use app\plugins\commission\models\CommissionRules;

class CommissionRuleOpenForm  extends BaseModel{

    public $goods_id;
    public $store_id;
    public $open;

    public function rules(){
        return [
            [['open'], 'required'],
            [['goods_id', 'store_id', 'open'], 'integer']
        ];
    }

    public function open(){
        if(!$this->validate()){
            return $this->responseErrorInfo();
        }

        try {
            if(!empty($this->goods_id)){
                $rule = CommissionRules::findOne([
                    "item_type" => "goods",
                    "item_id"   => $this->goods_id
                ]);
            }else{
                $rule = CommissionRules::findOne([
                    "item_type" => "checkout",
                    "item_id"   => $this->store_id
                ]);
            }

            if($rule){
                if($this->open){
                    $rule->is_delete = 0;
                }else{
                    $rule->is_delete = 1;
                    $rule->deleted_at = time();
                }
                if(!$rule->save()){
                    throw new \Exception($this->responseErrorMsg($rule));
                }
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg'  => '操作成功'
            ];
        }catch (\Exception $e){
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg'  => $e->getMessage()
            ];
        }
    }
}