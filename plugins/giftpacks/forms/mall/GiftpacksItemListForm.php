<?php
namespace app\plugins\giftpacks\forms\mall;

use app\core\ApiCode;
use app\models\BaseModel;
use app\models\Goods;
use app\models\GoodsWarehouse;
use app\models\Store;
use app\plugins\giftpacks\models\Giftpacks;
use app\plugins\giftpacks\models\GiftpacksItem;
use app\plugins\mch\models\Mch;

class GiftpacksItemListForm extends BaseModel{

    public $page;
    public $pack_id;
    public $keyword;
    public $sort_prop;
    public $sort_type;

    public function rules(){
        return array_merge(parent::rules(), [
            [['pack_id'], 'required'],
            [['page'], 'integer'],
            [['keyword', 'sort_prop', 'sort_type'], 'safe']
        ]);
    }

    public function getList() {
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }

        try {

            $query = GiftpacksItem::find()->alias("gpi");
            $query->innerJoin(["g" => Goods::tableName()], "g.id=gpi.goods_id");
            $query->innerJoin(["gw" => GoodsWarehouse::tableName()], "gw.id=g.goods_warehouse_id");
            $query->innerJoin(["s" => Store::tableName()], "s.id=gpi.store_id");

            $query->andWhere(["gpi.pack_id" => $this->pack_id, "gpi.is_delete" => 0]);

            if(!empty($this->keyword)){
                $query->andWhere([
                    "OR",
                    ["LIKE", "gpi.name", $this->keyword],
                    ["LIKE", "s.name", $this->keyword],
                    ["LIKE", "gw.name", $this->keyword]
                ]);
            }

            $orderBy = null;
            if(!empty($this->sort_prop)){

            }

            if(empty($orderBy)){
                $orderBy = "gpi.id " . (!$this->sort_type   ? "DESC" : "ASC");
            }

            $selects = ["gpi.*"];
            $selects[] = "(SELECT COUNT(*) FROM {{%plugin_giftpacks_order_item}} WHERE pack_item_id=gpi.id) AS order_item_num";
            $selects = array_merge($selects, [
                "g.price as goods_price", "s.name as store_name"
            ]);
            $query->select($selects);

            $list = $query->orderBy($orderBy)->page($pagination, 20)->asArray()->all();
            if($list){
                foreach($list as &$item){
                    if(!empty($item['expired_at'])){
                        $item['expired_at'] = date("Y-m-d", $item['expired_at']);
                    }else{
                        $item['expired_at'] = "";
                    }
                    $item['order_item_num'] = (int)$item['order_item_num'];
                    $item['stock'] = $item['max_stock'] - $item['order_item_num']; //TODO 剩余库存
                }
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'data' => [
                    'list' => $list ? $list : [],
                    'pagination' => $pagination
                ]
            ];
        }catch (\Exception $e){
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg'  => $e->getMessage()
            ];
        }
    }
}