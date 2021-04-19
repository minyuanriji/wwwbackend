<?php


namespace app\plugins\baopin\forms\api;


use app\core\ApiCode;
use app\models\BaseModel;
use app\models\DistrictData;
use app\models\Order;
use app\models\OrderDetail;
use app\models\Store;
use app\plugins\mch\models\Mch;

class ClosestStoreForm extends BaseModel {

    public $id;
    public $longitude;
    public $latitude;
    public $page;

    public function rules(){
        return [
            [['id', 'longitude', 'latitude'], 'required'],
            [['longitude', 'latitude'], 'string'],
            [['id', 'page'], 'integer']
        ];
    }

    public function search(){
        if(!$this->validate()){
            return $this->responseErrorInfo();
        }

        try {
            $order = Order::findOne($this->id);
            if(!$order || $order->is_delete){
                throw new \Exception("订单不存在");
            }

            if($order->order_type != "offline_baopin"){
                throw new \Exception("不是爆品订单");
            }

            $query = Store::find()->alias("s");
            $query->innerJoin("{{%plugin_baopin_mch_goods}} bmg", "bmg.mch_id=s.mch_id AND bmg.is_delete=0");
            $query->innerJoin("{{%order_detail}} od", "od.goods_id=bmg.goods_id");
            $query->innerJoin("{{%plugin_mch}} m", "m.id=s.mch_id");
            $query->andWhere([
                "od.order_id"     => $order->id,
                "m.is_delete"     => 0,
                "m.review_status" => Mch::REVIEW_STATUS_CHECKED
            ]);
            $query->groupBy("s.id");
            $selects = ["s.mch_id", "s.name", "s.address", "s.province_id", "s.city_id",
                "s.longitude", "s.latitude", "s.score", "s.cover_url"];
            $selects[] = "ST_Distance_sphere(point(s.longitude, s.latitude), point(".$this->longitude.", ".$this->latitude.")) as distance_mi";

            $rows = $query->select($selects)->orderBy("distance_mi ASC")
                          ->page($pagination, 10, max(1, (int)$this->page))
                          ->asArray()->all();
            if($rows){
                foreach($rows as &$item){

                    $item['province'] = "";
                    if(!empty($item['province_id'])){
                        $district = DistrictData::getDistrict($item['province_id']);
                        if($district){
                            $item['province'] = $district->name;
                        }
                    }

                    $item['city'] = "";
                    if(!empty($item['city_id'])){
                        $district = DistrictData::getDistrict($item['city_id']);
                        if($district){
                            $item['city'] = $district->name;
                        }
                    }

                    $item['distance_format'] = "0m";
                    if(empty($item['distance_mi']))
                        continue;
                    if($item['distance_mi'] < 1000){
                        $item['distance_format'] = intval($item['distance_mi']) . "m";
                    }else if($item['distance_mi'] >= 1000){
                        $item['distance_format'] = round(($item['distance_mi']/1000), 1) . "km";
                    }
                }
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg'  => '查询成功',
                'data' => [
                    'list'       => $rows ? $rows : [],
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