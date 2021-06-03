<?php
namespace app\plugins\baopin\forms\mall;

use app\core\ApiCode;
use app\helpers\ArrayHelper;
use app\models\BaseModel;
use app\models\Goods;
use app\models\GoodsWarehouse;
use app\models\Store;
use app\plugins\baopin\models\BaopinGoods;
use app\plugins\baopin\models\BaopinMchGoods;

class StoreGoodsListForm extends BaseModel{

    public $store_id;
    public $page;
    public $keyword;
    public $sort_prop;
    public $sort_type;

    public function rules(){
        return array_merge(parent::rules(), [
            [['store_id'], 'required'],
            [['page', 'store_id'], 'integer'],
            [['keyword', 'sort_prop', 'sort_type'], 'safe']
        ]);
    }

    public function getList(){
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }

        try {

            $store = Store::findOne($this->store_id);
            if(!$store || $store->is_delete){
                throw new \Exception("门店不存在");
            }

            $query = BaopinMchGoods::find()->alias("bmg");
            $query->innerJoin(["bp" => BaopinGoods::tableName()], "bp.goods_id=bmg.goods_id");
            $query->innerJoin(["g" => Goods::tableName()], "g.id=bmg.goods_id");
            $query->innerJoin(["gw" => GoodsWarehouse::tableName()], "gw.id=g.goods_warehouse_id");

            $query->andWhere(["bmg.store_id" => $this->store_id]);

            if (!empty($this->keyword)) {
                $query->andWhere([
                    'or',
                    ["g.id" => (int)$this->keyword],
                    ['LIKE', 'gw.name', $this->keyword]
                ]);
            }

            $select = ["g.id", "gw.name", "gw.cover_pic", "bmg.stock_num", "bmg.created_at"];

            $orderBy = null;
            if(!empty($this->sort_prop)){
                $this->sort_type = (int)$this->sort_type;
                if($this->sort_prop == "id"){
                    $orderBy = "g.id " . (!$this->sort_type ? "DESC" : "ASC");
                }
            }

            if(empty($orderBy)){
                $orderBy = "bmg.stock_num " . (!$this->sort_type   ? "DESC" : "ASC");
            }

            $query->orderBy($orderBy);

            $list = $query->select($select)->asArray()->page($pagination)->all();
            if($list){
                foreach($list as &$item){

                }
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '请求成功',
                'data' => [
                    'list'       => $list ? $list : [],
                    'pagination' => $pagination,
                    'store'      => ArrayHelper::toArray($store)
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