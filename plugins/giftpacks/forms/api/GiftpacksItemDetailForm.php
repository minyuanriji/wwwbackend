<?php

namespace app\plugins\giftpacks\forms\api;


use app\core\ApiCode;
use app\helpers\CityHelper;
use app\models\BaseModel;
use app\models\Goods;
use app\models\GoodsWarehouse;
use app\models\Store;
use app\plugins\giftpacks\models\GiftpacksItem;

class GiftpacksItemDetailForm extends BaseModel{

    public $item_id;
    public $city_id;
    public $longitude;
    public $latitude;

    public function rules(){
        return [
            [['item_id', 'longitude', 'latitude'], 'required'],
            [['city_id'], 'integer']
        ];
    }

    public function getDetail(){

        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }

        try {

            $detail = static::detail($this->item_id, $this->latitude, $this->longitude);

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'data' => [
                    'detail' => $detail
                ]
            ];
        }catch (\Exception $e){
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg'  => $e->getMessage()
            ];
        }
    }

    public static function detail($itemId, $lat, $lng){

        $query = GiftpacksItem::find()->alias("gpi");
        $query->where(["gpi.is_delete" => 0, "gpi.id" => $itemId]);
        $query->innerJoin(["s" => Store::tableName()], "s.id=gpi.store_id");
        $query->innerJoin(["g" => Goods::tableName()], "g.id=gpi.goods_id");
        $query->innerJoin(["gw" => GoodsWarehouse::tableName()], "gw.id=g.goods_warehouse_id");

        $selects = ["gpi.*"];
        $selects[] = "g.price as goods_price"; //商品价格
        $selects[] = "s.name as store_name"; //店铺名称
        $selects = array_merge($selects, [
            "s.mch_id", "s.score", "s.longitude", "s.latitude",
            "s.mobile", "s.address", "s.province_id", "s.city_id", "s.district_id", "s.cover_url"
        ]);
        $selects[] = "ST_Distance_sphere(point(s.longitude, s.latitude), point(".$lng.", ".$lat.")) as distance_mi";

        $item = $query->select($selects)->asArray()->one();
        if(!$item){
            throw new \Exception("无法获取大礼包商品详情信息");
        }

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

        $item['max_stock'] = (int)$item['max_stock'];
        $infos = [];
        if($item['usable_times'] > 0){
            $infos[] = "可使用" . $item['usable_times'] . "次";
        }else{
            $infos[] = "不限次数";
        }
        if($item['expired_at'] > 0){
            $expireTime = $item['expired_at'];
            if($item['limit_time'] > 0){
                $endTime = strtotime(date("Y-m-d 00:00:00")) + intval($item['limit_time']) * 3600 * 24;
                if($endTime < $expireTime){
                    $expireTime = $endTime;
                }
            }
            $infos[] = "下单后" . date("Y-m-d", $expireTime) . "失效";
            $item['expired_at'] = $expireTime;
        }else{
            $infos[] = "永久有效";
        }
        $item['infos'] = implode("，", $infos);

        if (empty($item['cover_url'])) {
            $item['cover_url'] = \Yii::$app->params['store_default_avatar'];
        }

        return $item;
    }
}