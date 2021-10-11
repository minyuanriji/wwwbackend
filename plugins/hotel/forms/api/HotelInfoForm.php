<?php

namespace app\plugins\hotel\forms\api;

use app\core\ApiCode;
use app\helpers\CityHelper;
use app\models\BaseModel;

class HotelInfoForm extends BaseModel
{
    public $longitude;
    public $latitude;
    public $city_name;

    public function rules()
    {
        return [
            [['city_name'], 'string'],
            [['longitude', 'latitude'], 'safe'],
        ];
    }

    public function getInfo()
    {
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }
        $resultData = [];
        try {
            //获取首页轮播图
            $resultData['banner'] = [
                [
                    'image' => 'https://www.mingyuanriji.cn/web/static/hotel/hotel-poster1.jpg',
                    'link' => '',
                ],
                [
                    'image' => 'https://www.mingyuanriji.cn/web/static/hotel/hotel-poster2.jpg',
                    'link' => '',
                ],
            ];

            //获取首页广告
            $resultData['advert'] = [
                'image' => 'https://www.mingyuanriji.cn/web/static/hotel/hotel-advert.jpg',
                'link' => '',
            ];

            if (!empty($this->city_name)) {
                $cityName = $this->city_name;
            } else {
                $CityInfo = CityHelper::getCityInfo($this->latitude, $this->longitude);
                if(isset($CityInfo['status']) && $CityInfo['status'] == 0){
                    $cityName = $CityInfo['result']['address_component']['city'];
                } else {
                    $cityName = '';
                }
            }

            $district = CityHelper::getDistrictName($cityName);
            if ($district) {
                $resultData['district'] = $district['district'];
                $resultData['city_id'] = $district['city_id'];
                $resultData['level'] = $district['level'];
            } else {
                $resultData['district'] = [];
                $resultData['city_id'] = [];
                $resultData['level'] = [];
            }

            $resultData['city_name'] = $cityName;
            return  $this->returnApiResultData(ApiCode::CODE_SUCCESS, '', $resultData);
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg' => $e->getMessage()
            ];
        }
    }
}