<?php
namespace app\plugins\commission\forms\mall;

use app\core\ApiCode;
use app\models\BaseModel;
use app\plugins\commission\models\CommissionRules;

class CommissionRuleListForm extends BaseModel{

    public $page;
    public $keyword;
    public $item_type;
    public $sort_prop;
    public $sort_type;

    public function rules(){
        return array_merge(parent::rules(), [
            [['page'], 'integer'],
            [['keyword', 'sort_prop', 'sort_type', 'item_type'], 'safe']
        ]);
    }

    public function getList(){

        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }

        $query = CommissionRules::find()->alias("crl");
        $query->leftJoin("{{%goods}} g", "g.id=crl.item_id AND crl.item_type='goods'");
        $query->leftJoin("{{%goods_warehouse}} gw", "gw.id=g.goods_warehouse_id");
        $query->leftJoin("{{%store}} s", "s.id=crl.item_id AND crl.item_type='checkout'");
        $query->leftJoin("{{%plugin_hotels}} h", "h.id=crl.item_id AND crl.item_type='hotel_3r'");
        $query->leftJoin("{{%plugin_addcredit_plateforms}} ap", "ap.id=crl.item_id AND crl.item_type='addcredit_3r'");
        $query->leftJoin("{{%plugin_giftpacks}} gp", "gp.id=crl.item_id AND crl.item_type='giftpacks'");

        $query->andWhere(["crl.is_delete" => 0]);

        if(!empty($this->item_type)){
            $query->andWhere(['crl.item_type' => $this->item_type]);
        }

        if (!empty($this->keyword)) {
            $query->andWhere([
                'OR',
                ["crl.item_id" => 0],
                ["crl.apply_all_item" => 1],
                "(g.id='".(int)$this->keyword."' AND crl.item_type='goods')",
                "(s.id='".(int)$this->keyword."' AND crl.item_type='checkout')",
                "(crl.mch_id='".(int)$this->keyword."' AND crl.item_type='checkout')",
                "(gw.name LIKE '%".addslashes($this->keyword)."%' AND crl.item_type='goods')",
                "(s.name LIKE '%".addslashes($this->keyword)."%' AND crl.item_type='checkout')",
                "(h.name LIKE '%".addslashes($this->keyword)."%' AND crl.item_type='hotel')",
                "(ap.name LIKE '%".addslashes($this->keyword)."%' AND crl.item_type='addcredit_3r')",
                "(gp.title LIKE '%".addslashes($this->keyword)."%' AND crl.item_type='giftpacks')",
            ]);
        }

        $select = [
            "crl.id", "crl.item_type", "crl.item_id", "crl.json_params", "crl.created_at", "crl.updated_at", "crl.apply_all_item",
            "g.id as goods_id", "gw.name as goods_name",
            "s.id as store_id", "s.name as store_name",
            "h.id as hotel_id", "h.name as hotel_name",
            "ap.id as addcredit_id", "ap.name as addcredit_name",
            "gp.id as giftpacks_id", "gp.title as giftpacks_name",
        ];

        $query->orderBy(["crl.apply_all_item" => SORT_DESC, "crl.id" => SORT_DESC]);

        $list = $query->select($select)->asArray()->page($pagination, 10, max(1, (int)$this->page))->all();

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '请求成功',
            'data' => [
                'list' => $list,
                'pagination' => $pagination,
            ]
        ];
    }

}