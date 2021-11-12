<?php

namespace app\plugins\giftpacks\forms\api;

use app\core\ApiCode;
use app\helpers\CityHelper;
use app\models\BaseModel;
use app\plugins\giftpacks\models\Giftpacks;

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
            $giftpacks = Giftpacks::findOne($this->pack_id);
            if(!$giftpacks || $giftpacks->is_delete){
                throw new \Exception("大礼包不存在");
            }

            $query = GiftpacksDetailForm::availableItemsQuery($giftpacks);

            $selects = ["gpi.*"];
            $selects[] = "g.price as goods_price"; //商品价格
            $selects[] = "s.name as store_name"; //店铺名称
            $selects = array_merge($selects, [
                "s.mch_id", "s.score", "s.longitude", "s.latitude",
                "s.mobile", "s.address", "s.province_id", "s.city_id", "s.district_id"
            ]);
            $selects[] = "ST_Distance_sphere(point(s.longitude, s.latitude), point(".$this->longitude.", ".$this->latitude.")) as distance_mi";

            $list = $query->select($selects)->page($pagination, 30, max(1, (int)$this->page))
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