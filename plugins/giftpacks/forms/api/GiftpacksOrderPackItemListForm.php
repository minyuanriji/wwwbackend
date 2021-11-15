<?php

namespace app\plugins\giftpacks\forms\api;


use app\core\ApiCode;
use app\helpers\CityHelper;
use app\models\BaseModel;
use app\models\Goods;
use app\models\GoodsWarehouse;
use app\models\Store;
use app\plugins\giftpacks\models\GiftpacksItem;
use app\plugins\giftpacks\models\GiftpacksOrder;
use app\plugins\giftpacks\models\GiftpacksOrderItem;

class GiftpacksOrderPackItemListForm extends BaseModel{

    public $page;
    public $order_id;
    public $city_id;
    public $longitude;
    public $latitude;

    public function rules(){
        return [
            [['order_id', 'longitude', 'latitude'], 'required'],
            [['city_id', 'page'], 'integer']
        ];
    }

    public function getList(){

        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }

        try {

            $query = GiftpacksOrderItem::find()->alias("goi")
                        ->innerJoin(["go" => GiftpacksOrder::tableName()], "go.id=goi.order_id")
                        ->innerJoin(["gpi" => GiftpacksItem::tableName()], "gpi.id=goi.pack_item_id AND gpi.is_delete=0")
                        ->innerJoin(["s" => Store::tableName()], "s.id=gpi.store_id")
                        ->innerJoin(["g" => Goods::tableName()], "g.id=gpi.goods_id")
                        ->innerJoin(["gw" => GoodsWarehouse::tableName()], "gw.id=g.goods_warehouse_id");

            $query->where([
                "goi.order_id" => $this->order_id,
                "go.mall_id"   => \Yii::$app->mall->id,
                "go.user_id"   => \Yii::$app->user->id,
                "go.is_delete" => 0
            ]);

            $selects = ["goi.pack_item_id", "goi.order_id", "goi.max_num", "goi.current_num", "goi.expired_at",
                "gpi.name", "gpi.cover_pic"
            ];
            $selects[] = "s.name as store_name"; //店铺名称
            $selects = array_merge($selects, [
                "s.mch_id", "s.score", "s.longitude", "s.latitude",
                "s.mobile", "s.address", "s.province_id", "s.city_id", "s.district_id"
            ]);
            $selects[] = "ST_Distance_sphere(point(s.longitude, s.latitude), point(".$this->longitude.", ".$this->latitude.")) as distance_mi";

            $query->orderBy("goi.id DESC");

            $list = $query->select($selects)->page($pagination, 999, max(1, (int)$this->page))
                ->asArray()->all();
            if($list){
                foreach($list as &$item){

                    $cityData = CityHelper::reverseData($item['district_id'], $item['city_id'], $item['province_id']);
                    $item['province'] = !empty($cityData['province']) ? $cityData['province']['name'] : "";
                    $item['city'] = !empty($cityData['city']) ? $cityData['city']['name'] : "";
                    $item['district'] = !empty($cityData['district']) ? $cityData['district']['name'] : "";

                    if($item['distance_mi'] > 1000){
                        $item['distance_format'] = round(($item['distance_mi']/1000), 1) . "km";
                    }else{
                        $item['distance_format'] = intval($item['distance_mi']) . "m";
                    }
                    unset($item['distance_mi']);

                    $item['score'] = number_format($item['score'], 1);

                    $infos = [];
                    if($item['max_num'] > 0){
                        $infos[] = "还剩".$item['current_num']."次";
                    }else{
                        $infos[] = "不限次数";
                    }
                    if($item['expired_at'] > 0){
                        $infos[] = date("Y-m-d", $item['expired_at']) . "到期";
                    }else{
                        $infos[] = "永久有效";
                    }
                    $item['infos'] = implode("，", $infos);

                    if(($item['max_num'] > 0 && $item['current_num'] <= 0) || ($item['expired_at'] > 0 && $item['expired_at'] < time())){
                        $item['is_available'] = 0;
                    }else{
                        $item['is_available'] = 1;
                    }
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