<?php

namespace app\plugins\mch\forms\api\mana;

use app\core\ApiCode;
use app\helpers\ArrayHelper;
use app\models\BaseModel;
use app\models\DistrictData;
use app\models\Store;
use app\plugins\mch\controllers\api\mana\MchAdminController;

class MchManaInfoUpdateForm extends BaseModel {

    public $cover_url;
    public $name;
    public $province_id;
    public $city_id;
    public $district_id;
    public $longitude;
    public $latitude;
    public $address;

    public function rules(){
        return array_merge(parent::rules(), [
            [['name', 'cover_url', 'province_id', 'city_id', 'longitude', 'latitude', 'address'], 'required'],
            [['province_id', 'city_id'], 'integer'],
            [['cover_url', 'name', 'longitude', 'latitude', 'address'], 'string'],
            [['district_id'], 'safe']
        ]);
    }

    public function save(){

        if(!$this->validate()){
            return $this->responseErrorInfo();
        }

        try {

            $mchStore = Store::findOne([
                "mch_id" => MchAdminController::$adminUser['mch_id']
            ]);
            if(!$mchStore || $mchStore->is_delete){
                throw new \Exception("无法获取店铺信息");
            }

            $this->checkDistrict();

            $pattern = "/\d+\.\d+/";
            if(!preg_match($pattern, $this->latitude) || !preg_match($pattern, $this->longitude)){
                throw new \Exception("经纬度坐标格式有误");
            }

            $mchStore->cover_url   = $this->cover_url;
            $mchStore->name        = $this->name;
            $mchStore->province_id = $this->province_id;
            $mchStore->city_id     = $this->city_id;
            $mchStore->district_id = $this->district_id;
            $mchStore->longitude   = $this->longitude;
            $mchStore->latitude    = $this->latitude;
            $mchStore->address     = $this->address;
            if(!$mchStore->save()){
                throw new \Exception($this->responseErrorMsg($mchStore));
            }

            $detail = ArrayHelper::toArray($mchStore);
            $detail['pic_url'] = json_decode($detail['pic_url'], true);
            if(isset($detail['pic_url'][0]) && empty($detail['pic_url'][0])){
                unset($detail['pic_url'][0]);
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg'  => '请求成功',
                'data' => [
                    'detail' => $detail
                ]
            ];
        }catch (\Exception $e){
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg' => $e->getMessage(),
            ];
        }
    }

    /**
     * 检查地区数据
     */
    protected function checkDistrict(){
        $districtData = DistrictData::getTerritorial();

        $districtData = array_combine(array_column($districtData, 'id'), $districtData) ;
        if(!isset($districtData[$this->province_id])){
            throw new \Exception('省份信息选择有误！');
        }

        $districtData = $districtData[$this->province_id]['list'];
        $districtData = array_combine(array_column($districtData, 'id'), $districtData) ;
        if(!isset($districtData[$this->city_id])){
            throw new \Exception('城市信息选择有误！');
        }

        $districtData = $districtData[$this->city_id]['list'];
        $districtData = array_combine(array_column($districtData, 'id'), $districtData) ;
        if($this->district_id && !isset($districtData[$this->district_id])){
            throw new \Exception('区信息选择有误！');
        }


    }
}