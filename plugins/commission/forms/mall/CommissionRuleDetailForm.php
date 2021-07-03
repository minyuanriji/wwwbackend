<?php
namespace app\plugins\commission\forms\mall;


use app\core\ApiCode;
use app\helpers\ArrayHelper;
use app\models\BaseModel;
use app\models\Goods;
use app\models\Store;
use app\plugins\commission\models\CommissionRuleChain;
use app\plugins\commission\models\CommissionRules;

class CommissionRuleDetailForm extends BaseModel{

    public $id;
    public $store_id;
    public $goods_id;

    public function rules(){
        return [
            [['id', 'goods_id', 'store_id'], 'integer']
        ];
    }

    public function getDetail(){

        if(!$this->validate()){
            return $this->responseErrorInfo();
        }

        try {
            if(!empty($this->goods_id)) {
                $rule = CommissionRules::findOne([
                    "item_type" => "goods",
                    "item_id"   => $this->goods_id
                ]);
            }elseif(!empty($this->store_id)){
                $rule = CommissionRules::findOne([
                    "item_type" => "checkout",
                    "item_id"   => $this->store_id
                ]);
            }else{
                $rule = CommissionRules::findOne($this->id);
            }

            if(!$rule){
                throw new \Exception("无法获取规则记录");
            }

            $rows = CommissionRuleChain::find()->where([
                "rule_id" => $rule->id
            ])->asArray()->all();

            $ruleData = ArrayHelper::toArray($rule);
            //$ruleData['commission_type'] = (string)$ruleData['commission_type'];

            $ruleData['goods_name'] = "";
            $ruleData['goods_pic']  = "";
            $ruleData['store_name'] = "";
            $ruleData['store_pic']  = "";

            if(!$ruleData['apply_all_item']){
                if($ruleData['item_type'] == "goods"){
                    $goods = Goods::find()->with("goodsWarehouse")->where([
                        "id" => $ruleData['item_id']
                    ])->asArray()->one();
                    if($goods && $goods['goodsWarehouse']){
                        $ruleData['goods_name'] = $goods['goodsWarehouse']['name'];
                        $ruleData['goods_pic']  = $goods['goodsWarehouse']['cover_pic'];
                    }else{
                        $ruleData['item_id'] = 0;
                    }
                }else{
                    $store = Store::findOne($ruleData['item_id']);
                    if($store){
                        $ruleData['store_name'] = $store->name;
                        $ruleData['store_pic']  = $store->cover_url;
                    }else{
                        $ruleData['item_id'] = 0;
                    }
                }
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'data' => [
                    'rule'   => $ruleData,
                    'chains' => $rows ? $rows : [],
                    'store' => $this->store_id ? [$this->getStore($this->store_id)] : []
                ]
            ];
        }catch (\Exception $e){
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg'  => $e->getMessage()
            ];
        }
    }

    public function getStore ($store_id)
    {
        $query = CommissionRules::find()->alias("cr");
        $query->leftJoin("{{%plugin_commission_rule_chain}} crc", "cr.id=crc.rule_id");
        $query->andWhere([
            "AND",
            ["cr.item_type"  => 'store'],
            ["cr.item_id"    => $store_id],
        ]);
        return $query->select(["cr.*", "crc.level", "crc.commisson_value"])->asArray()->one();
    }

}