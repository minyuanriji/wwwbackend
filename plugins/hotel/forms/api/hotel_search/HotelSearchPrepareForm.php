<?php
namespace app\plugins\hotel\forms\api\hotel_search;


use app\core\ApiCode;
use app\helpers\CityHelper;
use app\helpers\TencentMapHelper;
use app\models\DistrictData;
use app\plugins\hotel\models\Hotels;

class HotelSearchPrepareForm extends HotelSearchForm {

    public $city_id;    //所在城市
    public $start_date; //起始日期日期
    public $days;       //预订天数
    public $type;       //酒店类型：in国内、hour钟点房、bb民宿
    public $keyword;    //关键词
    public $s_price;    //起步价
    public $e_price;    //截止价
    public $level;      //星级1-10
    public $lng;        //经度
    public $lat;        //纬度


    public function rules(){
        return [
            [['city_id', 'type', 'start_date', 'days'], 'required'],
            [['days'], 'integer', 'min' => 1],
            [['city_id'], 'integer'],
            [['lng', 'lat', 'keyword', 's_price', 'e_price', 'level'], 'safe']
        ];
    }

    /**
     * 判断是否有历史查询数据
     * @return boolean
     */
    public function hasData(){
        $searchId = $this->generateSearchId();
        $hotelIds = $this->getFoundHotelIds($searchId);
        return !empty($hotelIds);
    }

    /**
     * 返回历史数据
     * @return array
     */
    public function history(){
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'data' => [
                'history'   => 1,
                'search_id' => $this->generateSearchId()
            ]
        ];
    }

    public function prepare(){
        if(!$this->validate()){
            return $this->responseErrorInfo();
        }

        try {
            if($this->city_id <= 0 && !empty($this->lng) && !empty($this->lat)){
                $poi = TencentMapHelper::toPoi($this->host_info, $this->lng, $this->lat);
                $citySearch = CityHelper::likeSearch($poi['province'], $poi['city'], $poi['district']);
                $cityId = isset($citySearch['city_id']) ? $citySearch['city_id'] : 0;
                $provinceId = isset($citySearch['province_id']) ? $citySearch['province_id'] : 0;
            }else{
                $districtArr = DistrictData::getArr();
                $city = isset($districtArr[$this->city_id]) ? $districtArr[$this->city_id] : null;
                if(!$city || $city['level'] != "city"){
                    throw new \Exception("城市信息选择错误");
                }
                $cityId = $city['id'];
                $provinceId = $city['parent_id'];
            }

            $todayStartTime = strtotime(date("Y-m-d") . " 00:00:00");
            $startTime = strtotime($this->start_date);

            if($startTime < $todayStartTime){
                throw new \Exception("起始日期不正确");
            }

            $query = Hotels::find()->alias("ho")->where([
                "is_open"     => 1,
                "is_booking"  => 1,
                "is_delete"   => 0,
                "city_id"     => $cityId,
                "province_id" => $provinceId
            ]);

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

            $rows = $query->asArray()->select("id")->all();

            $hotelIds = [];
            foreach($rows as $row){
                $hotelIds[] = $row['id'];
            }

            $prepareId = $this->writePrepareData($hotelIds);

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'data' => [
                    'history'    => 0,
                    'prepare_id' => $prepareId
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