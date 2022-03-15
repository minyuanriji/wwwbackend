<?php
namespace app\plugins\shopping_voucher\forms\api;

use app\core\ApiCode;
use app\forms\api\APICacheDataForm;
use app\forms\api\ICacheForm;
use app\models\BaseModel;
use app\models\Goods;
use app\models\GoodsWarehouse;
use app\plugins\shopping_voucher\models\ShoppingVoucherTargetGoods;

class ShoppingVoucheTargetGoodsListForm extends BaseModel implements ICacheForm {

    public $page;
    public $limit;
    public $mall_id;
    public $keyword;

    public function rules(){
        return [
            [['page', 'limit'], 'integer'],
            [['keyword'], 'trim']
        ];
    }

    public function getQuery(){
        $query = Goods::find()->alias('g');
        $query->innerJoin(["svtg" => ShoppingVoucherTargetGoods::tableName()], "svtg.goods_id=g.id");
        $query->innerJoin(['gw' => GoodsWarehouse::tableName()], 'gw.id=g.goods_warehouse_id');

        $query->where([
            "g.is_delete"    => 0,
            "g.status"       => Goods::STATUS_ON,
            "svtg.is_delete" => 0
        ]);

        if($this->keyword){
            $query->andWhere(["LIKE", "gw.name", $this->keyword]);
        }

        return $query;
    }

    /**
     * @return APICacheDataForm
     */
    public function getSourceDataForm(){
        if(!$this->validate()){
            return $this->responseErrorInfo();
        }

        try {

            $query = $this->getQuery();
            $query->orderBy("g.sort DESC, g.id DESC");

            $selects = ["g.id", "gw.name", "gw.cover_pic", "g.price", "gw.original_price",  "svtg.voucher_price"];
            $list = $query->asArray()
                ->select($selects)
                ->page($pagination, $this->limit, $this->page)->all();
            if($list){
                foreach($list as &$item){

                }
            }

            return new APICacheDataForm([
                "sourceData" => [
                    'code'  => ApiCode::CODE_SUCCESS,
                    'count' => isset($pagination->total_count) ? $pagination->total_count : 0,
                    'data'  => $list
                ]
            ]);
        }catch (\Exception $e){
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg'  => $e->getMessage()
            ];
        }
    }

    /**
     * @return array
     */
    public function getCacheKey(){
        $keys = [$this->mall_id, $this->limit, $this->page, $this->keyword];
        return $keys;
    }
}