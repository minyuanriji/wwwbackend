<?php
namespace app\plugins\commission\forms\mall;

use app\core\ApiCode;
use app\models\BaseModel;
use app\models\Goods;
use app\models\Store;
use app\plugins\commission\models\CommissionRuleChain;
use app\plugins\commission\models\CommissionRules;

class CommissionRuleEditForm extends BaseModel {

    public $item_type;
    public $item_id;
    public $apply_all_item;

    public $commission_type;
    public $commission_chains_json;

    public function rules(){
        return [
            [['item_type'], 'required'],
            [['item_id', 'apply_all_item', 'commission_type'], 'integer'],
            [['commission_chains_json'], 'string']
        ];
    }

    public function save(){
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }

        try {

            if(!$this->apply_all_item && empty($this->item_id)){
                if($this->item_type == 'goods'){
                    throw new \Exception("请设置商品");
                }else{
                    throw new \Exception("请设置门店");
                }
            }

            if(!empty($this->item_id)){
                if($this->item_type == 'goods'){
                    $itemObject = Goods::findOne($this->item_id);
                    if(!$itemObject){
                        throw new \Exception("商品不存在");
                    }
                }else{
                    $itemObject = Store::findOne($this->item_id);
                    if(!$itemObject){
                        throw new \Exception("门店不存在");
                    }
                }
            }

            if($this->apply_all_item){
                $this->item_id        = 0;
                $this->apply_all_item = 1;
            }

            $rule = CommissionRules::findOne([
                "mch_id"         => 0,
                "item_type"      => $this->item_type,
                "item_id"        => $this->item_id,
                "apply_all_item" => (int)$this->apply_all_item
            ]);
            if(!$rule){
                $rule = new CommissionRules([
                    "mall_id"        => \Yii::$app->mall->id,
                    "mch_id"         => 0,
                    "item_type"      => $this->item_type,
                    "item_id"        => $this->item_id,
                    "apply_all_item" => (int)$this->apply_all_item,
                    "created_at"     => time()
                ]);
            }

            $rule->is_delete = 0;
            $rule->commission_type = in_array($this->commission_type, [1, 2]) ? $this->commission_type : 1;
            $rule->json_params = "{}";
            $rule->updated_at = time();
            if(!$rule->save()){
                throw new \Exception($this->responseErrorMsg($rule));
            }

            $chainList = json_decode($this->commission_chains_json, true);
            if($chainList){
                foreach($chainList as $item){
                    $roleType = $item['role_type'];
                    if(empty($item['unique_key']) || !in_array($roleType, ['store', 'partner', 'user', 'branch_office']))
                        continue;
                    $ruleChain = CommissionRuleChain::findOne([
                        "rule_id"    => $rule->id,
                        "role_type"  => $item['role_type'],
                        "level"      => $item['level'],
                        "unique_key" => strtolower($item['unique_key'])
                    ]);
                    if(!$ruleChain){
                        $ruleChain = new CommissionRuleChain([
                            "mall_id"    => $rule->mall_id,
                            "rule_id"    => $rule->id,
                            "role_type"  => $item['role_type'],
                            "level"      => $item['level'],
                            "unique_key" => strtolower($item['unique_key']),
                            "created_at" => time()
                        ]);
                    }
                    $ruleChain->commisson_value = max(0, $item['commisson_value']);
                    $ruleChain->updated_at = time();
                    if(!$ruleChain->save()){
                        throw new \Exception($this->responseErrorMsg($ruleChain));
                    }
                }
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg'  => '保存成功'
            ];

        }catch (\Exception $e){
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg'  => $e->getMessage()
            ];
        }
    }

}