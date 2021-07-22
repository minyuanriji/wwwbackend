<?php

namespace app\plugins\giftpacks\forms\api;

use app\core\ApiCode;
use app\models\BaseModel;
use app\models\Goods;
use app\models\GoodsWarehouse;
use app\models\Store;
use app\plugins\giftpacks\models\GiftpacksItem;
use app\plugins\giftpacks\models\GiftpacksOrderItem;

class GiftpacksItemListForm extends BaseModel{

    public $page;
    public $pack_id;
    public $city_id;
    public $longitude;
    public $latitude;

    public function rules(){
        return [
            [['pack_id', 'longitude', 'latitude'], 'required'],
            [['city_id', 'page'], 'integer']
        ];
    }

    public function getList(){

        if(!$this->validate()){
            return $this->responseErrorInfo();
        }

        try {

            $query = GiftpacksItem::find()->alias("gpi");
            $query->innerJoin(["s" => Store::tableName()], "s.id=gpi.store_id");
            $query->innerJoin(["g" => Goods::tableName()], "g.id=gpi.goods_id");
            $query->innerJoin(["gw" => GoodsWarehouse::tableName()], "gw.id=g.goods_warehouse_id");
            $query->leftJoin(["goi" => GiftpacksOrderItem::tableName()], "goi.pack_item_id=gpi.id");

            $query->where([ "gpi.pack_id" => $this->pack_id, "gpi.is_delete" => 0]);

            //过期、库存为0的不显示
            $query->andWhere([
                "OR",
                "gpi.expired_at=0",
                "gpi.expired_at > '".time()."'"
            ]);

            $selects = ["gpi.*"];
            $selects[] = "g.price as goods_price"; //商品价格
            $selects[] = "s.name as store_name"; //店铺名称
            $selects = array_merge($selects, ["s.mch_id", "s.score", "s.longitude", "s.latitude"]);
            $selects[] = "ST_Distance_sphere(point(s.longitude, s.latitude), point(".$this->longitude.", ".$this->latitude.")) as distance_mi";

            $query->select($selects)
                  ->orderBy("gpi.updated_at DESC")
                  ->page($pagination, 10, max(1, (int)$this->page));
            $query->groupBy("gpi.id HAVING count(gpi.id) < gpi.max_stock ");

            $list = $query->asArray()->all();

            if($list){
                foreach($list as &$item){
                    if($item['distance_mi'] > 1000){
                        $item['distance_format'] = round(($item['distance_mi']/1000), 1) . "km";
                    }else{
                        $item['distance_format'] = intval($item['distance_mi']) . "m";
                    }
                    unset($item['distance_mi']);

                    $item['score'] = number_format($item['score'], 1);

                    $item['max_stock'] = (int)$item['max_stock'];
                    $infos = [];
                    if($item['usable_times'] > 0){
                        $infos[] = "可使用" . $item['usable_times'] . "次";
                    }else{
                        $infos[] = "不限次数";
                    }
                    if($item['expired_at'] > 0){
                        $infos[] = date("Y-m-d", $item['expired_at']) . "到期";
                    }else{
                        $infos[] = "永久有效";
                    }
                    $item['infos'] = implode("，", $infos);
                }
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'data' => [
                    'list'       => $list ? $list : [],
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