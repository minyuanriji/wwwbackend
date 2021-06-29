<?php
namespace app\forms\api;


use app\core\ApiCode;
use app\models\BaseModel;
use app\helpers\CityHelper;

class DistrictLikeSearchForm extends BaseModel{

    public $province;
    public $city;
    public $district;

    public function rules(){
        return [
            [['province', 'city'], 'required'],
            [['district'], 'safe']
        ];
    }

    public function search(){
        if(!$this->validate()){
            return $this->responseErrorInfo();
        }

        $data = CityHelper::likeSearch($this->province, $this->city, $this->district);

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'data' => $data
        ];
    }
}