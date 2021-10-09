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
                'https://www.mingyuanriji.cn/web//uploads/images/original/20210922/679cb9aa849b5828c15e22fabb67726c.jpg',
                'https://www.mingyuanriji.cn/web//uploads/images/original/20210508/dacd08c809c1314a6c4071d45ca7b567.jpg',
            ];

            //获取首页广告
            $resultData['advert'] = 'https://www.mingyuanriji.cn/web//uploads/images/original/20210818/db01559d840db23c955f9ce686ad1384.jpg';

            if (!empty($this->city_name)) {
                $cityName = $this->city_name;
                $cityLike = true;
            } else {
                $CityInfo = CityHelper::getCityInfo($this->latitude, $this->longitude);
                if(isset($CityInfo['status']) && $CityInfo['status'] == 0){
                    $cityName = $CityInfo['result']['address_component']['city'];
                    $cityLike = false;
                } else {
                    $cityName = '';
                    $cityLike = false;
                }
            }

            $district = CityHelper::getDistrictName($cityName, $cityLike);
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