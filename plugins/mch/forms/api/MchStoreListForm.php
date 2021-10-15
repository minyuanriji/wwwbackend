<?php
namespace app\plugins\mch\forms\api;


use app\core\ApiCode;
use app\forms\api\APICacheDataForm;
use app\forms\api\ICacheForm;
use app\helpers\CityHelper;
use app\models\BaseModel;
use app\models\Store;
use app\plugins\mch\models\Mch;
use app\plugins\mch\models\MchCommonCat;
use app\plugins\shopping_voucher\models\ShoppingVoucherFromStore;

class MchStoreListForm extends BaseModel implements ICacheForm {

    public $page;
    public $cat_id;
    public $keyword;
    public $city_id;
    public $region_id;
    public $longitude;
    public $latitude;
    public $sort_by;
    public $distance;

    public function rules(){
        return [
            [['cat_id', 'page'], 'integer'],
            [['keyword', 'city_id', 'region_id', 'sort_by', 'distance', 'longitude', 'latitude', 'sort_by'], 'safe'],
        ];
    }

    public function getCacheKey(){
        $rawSql = $this->getQuery()->createCommand()->getRawSql();
        $keys[] = md5(strtolower($rawSql));
        return $keys;
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
            $query->page($pagination, 15, max(1, (int)$this->page));
            $list = $query->asArray()->all();
            if($list){
                foreach($list as &$item){

                    if(empty($item['cover_url'])){
                        $item['cover_url'] =  $this->host_info . "/web/static/header-logo.png";
                    }

                    $item['score'] = sprintf("%01.1f",floatval($item['score']));

                    if($item['distance_mi'] < 0){
                        $item['distance_mi'] = "距离未知";
                    }elseif($item['distance_mi'] < 1000){
                        $item['distance_format'] = intval($item['distance_mi']) . "m";
                    }else if($item['distance_mi'] >= 1000){
                        $item['distance_format'] = round(($item['distance_mi']/1000), 1) . "km";
                    }
                    $cityData = CityHelper::reverseData($item['district_id'], $item['city_id'], $item['province_id']);
                    $item['province']    = isset($cityData['province']['name']) ? $cityData['province']['name'] : "";
                    $item['city']        = isset($cityData['city']['name']) ? $cityData['city']['name'] : "";
                    $item['district']    = isset($cityData['district']['name']) ? $cityData['district']['name'] : "";
                    $item['region_name'] = $item['district'] ? $item['district'] : ($item['city'] ? $item['city'] : $item['province']);

                    $item['remark'] = "";
                    if($item['shopping_voucher_give_value']){
                        $item['remark'] = "付100送".$item['shopping_voucher_give_value']."购物券";
                    }

                }
            }

            return new APICacheDataForm([
                "sourceData" => [
                    'code' => ApiCode::CODE_SUCCESS,
                    'data' => [
                        'list'       => $list,
                        'pagination' => $pagination
                    ]
                ]
            ]);
        }catch (\Exception $e){
            return ['code' => ApiCode::CODE_FAIL, 'msg' => $e->getMessage()];
        }
    }

    /**
     * 生成查询对象
     * @return \app\models\BaseActiveQuery
     */
    public function getQuery(){

        $query = Store::find()->alias("s");
        $query->innerJoin(["m" => Mch::tableName()], "m.id=s.mch_id");
        $query->leftJoin(["c" => MchCommonCat::tableName()], "c.id=m.mch_common_cat_id");
        $query->leftJoin(["svfs" => ShoppingVoucherFromStore::tableName()], "svfs.store_id=s.id AND svfs.is_delete=0");
        $query->andWhere([
            "AND",
            ["m.review_status" => Mch::REVIEW_STATUS_CHECKED],
            ["m.is_delete"     => 0],
            "s.longitude IS NOT NULL",
            "s.latitude IS NOT NULL"
        ]);

        if($this->cat_id){
            $query->andWhere(["m.mch_common_cat_id" => $this->cat_id]);
        }

        if($this->keyword){
            $query->andWhere(["LIKE", "s.name", $this->keyword]);
        }

        if($this->distance && $this->longitude && $this->latitude){ //距离范围
            $distanceMi = intval($this->distance) * 1000;
            $query->andWhere("ST_Distance_sphere (
                point (longitude, latitude),
                point ({$this->longitude}, {$this->latitude})
            ) <= '{$distanceMi}'");
        }elseif($this->region_id){ //按照市所在区搜索
            $cityData = CityHelper::reverseData($this->region_id);
            if(isset($cityData['province']['id'])){
                $query->andWhere(["s.province_id" => $cityData['province']['id']]);
            }
            if(isset($cityData['city']['id'])){
                $query->andWhere(["s.city_id" => $cityData['city']['id']]);
            }
            if(isset($cityData['district']['id'])){
                $query->andWhere(["s.district_id" => $cityData['district']['id']]);
            }
        }elseif($this->city_id){
            $query->andWhere(["s.city_id" => $this->city_id]);
        }

        $selects = ["s.id", "s.mall_id", "s.cover_url", "s.name", "s.mobile", "s.address", "s.province_id", "s.city_id", "s.district_id",
            "s.longitude", "s.latitude", "s.score", "m.mch_common_cat_id", "c.name as cat_name", "svfs.give_value as shopping_voucher_give_value"
        ];

        if($this->longitude && $this->latitude){
            $selects[] = "ST_Distance_sphere(point(longitude, latitude), point(".$this->longitude.", ".$this->latitude.")) as distance_mi";
        }else{
            $selects[] = "-1 as distance_mi";
        }

        $query->select($selects);

        $sortSql = "";
        if($this->sort_by == "score") { //好评
            $sortSql = "s.score DESC";
        }elseif($this->sort_by == "new"){ //新店
            $sortSql = "s.created_at DESC,distance_mi ASC";
        }else{
            $sortSql = "distance_mi ASC,s.id DESC";
        }
        $query->orderBy($sortSql);

        return $query;
    }


}