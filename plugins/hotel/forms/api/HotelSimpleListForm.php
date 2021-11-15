<?php
namespace app\plugins\hotel\forms\api;

use app\controllers\api\ApiController;
use app\core\ApiCode;
use app\forms\api\APICacheDataForm;
use app\forms\api\ICacheForm;
use app\models\BaseModel;
use app\models\DistrictData;
use app\plugins\hotel\forms\api\hotel_search\HotelSearchForm;
use app\plugins\hotel\models\Hotels;
use app\plugins\hotel\models\HotelSearch;

class HotelSimpleListForm extends BaseModel implements ICacheForm {

    public $page;
    public $lng;
    public $lat;
    public $search_id;
    public $city_id;
    public $order_by_name;
    public $sort_type;
    public $keyword;    //关键词
    public $s_price;    //起步价
    public $e_price;    //截止价
    public $level;      //星级1-10
    public $start_date; //起始日期日期
    public $type;       //酒店类型：in国内、hour钟点房、bb民宿
    public $days;       //预订天数

    public function rules(){
        return [
            [['page', 'city_id', 'days'], 'integer'],
            [['lng', 'lat', 'search_id', 'order_by_name', 'sort_type'], 'string'],
            [['type', 'start_date', 'keyword', 's_price', 'e_price', 'level'], 'safe']
        ];
    }

    public function getSourceDataForm(){


        if(!$this->validate()){
            return $this->responseErrorInfo();
        }

        try {

            if(empty($this->lat) || empty($this->lng)){
                $this->lng = ApiController::$commonData['city_data']['longitude'];
                $this->lat = ApiController::$commonData['city_data']['latitude'];
            }

            if(empty($this->city_id) || intval($this->city_id) <= 0){
                $this->city_id = ApiController::$commonData['city_data']['city_id'];
            }

            if($this->city_id > 0){
                $districtArr = DistrictData::getArr();
                if(!isset($districtArr[$this->city_id])){
                    throw new \Exception("所在城市定位异常");
                }
            }

            $query = $this->getQuery();

            $rows = $query->page($pagination, 10, max(1, (int)$this->page))
                          ->asArray()->all();
            foreach($rows as &$row){
                $row['type_text'] = static::getTypeText($row['type']);

                $row['distance']      = "N";
                $row['distance_unit'] = "N";

                if($row['distance_mi'] < 1000){
                    $row['distance'] = intval($row['distance_mi']);
                    $row['distance_unit'] = "m";
                }else if($row['distance_mi'] >= 1000){
                    $row['distance'] = round(($row['distance_mi']/1000), 1);
                    $row['distance_unit'] = "km";
                }

                unset($row['distance_mi']);
            }

            return new APICacheDataForm([
                "sourceData" => [
                    'code' => ApiCode::CODE_SUCCESS,
                    'data' => [
                        'list'       => $rows ? $rows : [],
                        'pagination' => $pagination
                    ]
                ]
            ]);

        }catch (\Exception $e){
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg'  => $e->getMessage()
            ];
        }
    }

    private function getQuery(){
        $query = Hotels::find()->alias("ho")->where([
            "ho.is_delete"  => 0,
            "ho.is_open"    => 1,
            "ho.is_booking" => 1,
            "ho.mall_id"    => \Yii::$app->mall->id
        ]);

        if(!empty($this->search_id)){
            $search = HotelSearch::findOne([
                "search_id" => $this->search_id
            ]);
            if(!$search){
                throw new \Exception("搜索异常，请重新搜索");
            }
            $content = !empty($search->content) ? json_decode($search->content, true) : [];
            $foundHotelIds = [];
            if(isset($content['found_ids'])){
                $foundHotelIds = $content['found_ids'];
            }
            $query->andWhere(["IN", "id", $foundHotelIds]);
            $attrs = isset($content['attrs']) ? $content['attrs'] : [];
            if(!empty($attrs['lng']) && !empty($attrs['lat'])){
                $this->lng = (string)$attrs['lng'];
                $this->lat = (string)$attrs['lat'];
            }
        }else{
            if($this->city_id){
                $query->andWhere([
                    "city_id" => $this->city_id
                ]);
            }
        }

        if(!empty($this->keyword)){
            $query->andWhere([
                "OR",
                ["LIKE", "ho.name", $this->keyword],
                ["LIKE", "ho.address", $this->keyword],
                "FIND_IN_SET('".$this->keyword."', ho.tag)"
            ]);
        }

        if(!empty($this->s_price)){
            $query->andWhere([">=", "ho.price", floatval($this->s_price)]);
        }

        if(!empty($this->e_price)){
            $query->andWhere(["<=", "ho.price", floatval($this->e_price)]);
        }

        $selects = ["ho.id", "ho.thumb_url", "ho.name", "ho.type", "ho.cmt_grade", "ho.cmt_num", "ho.price"];
        $selects[] = "ST_Distance_sphere(point(ho.tx_lng, ho.tx_lat), point(".$this->lng.", ".$this->lat.")) as distance_mi";

        $order_by = "distance_mi ASC";
        if ($this->order_by_name && $this->sort_type) {
            $order_by = $this->order_by_name . " " . $this->sort_type;
        }
        $query->orderBy($order_by);
        $query->select($selects);

        return $query;
    }

    private static function getTypeText($type){
        $typeTexts = ['luxe' => '豪华型', 'comfort' => '舒适型', 'eco' => '经济型'];
        return isset($typeTexts[$type]) ? $typeTexts[$type] : "";
    }

    /**
     * @return string
     */
    public function getCacheKey(){
        $rawSql = $this->getQuery()->createCommand()->getRawSql();
        $keys[] = md5(strtolower($rawSql));
        $keys[] = $this->page;
        $keys[] = $this->lat;
        $keys[] = $this->lng;
        return $keys;
    }
}