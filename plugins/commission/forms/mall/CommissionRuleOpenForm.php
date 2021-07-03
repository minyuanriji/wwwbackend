<?php
namespace app\plugins\commission\forms\mall;

use app\core\ApiCode;
use app\models\BaseModel;
use app\plugins\commission\models\CommissionRules;

class CommissionRuleOpenForm  extends BaseModel{

    public $goods_id;
    public $store_id;
    public $open;
    public $item_type;

    public function rules(){
        return [
            [['open'], 'required'],
            [['item_type'], 'string'],
            [['goods_id', 'store_id', 'open'], 'integer']
        ];
    }

    public function open(){
        if(!$this->validate()){
            return $this->responseErrorInfo();
        }

        try {
            $where = [];
            if(!empty($this->goods_id)){
                $where['item_type'] = 'goods';
                $where['item_id'] = $this->goods_id;
            }elseif (isset($this->item_type) && $this->item_type == 'store') {
                $where['item_type'] = $this->item_type;
                $where['item_id'] = $this->store_id;
            } else {
                $where['item_type'] = 'checkout';
                $where['item_id'] = $this->store_id;
            }
            $rule = CommissionRules::findOne($where);

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