<?php

namespace app\plugins\perform_distribution\forms\mall;

use app\core\ApiCode;
use app\models\BaseModel;
use app\plugins\perform_distribution\models\PerformDistributionRegion;

class RegionEditForm extends BaseModel{

    public $id;
    public $name;
    public $region_id;
    public $province_id;
    public $city_id;
    public $district_id;
    public $address;

    public function rules(){
        return [
            [['name', 'region_id'], 'required'],
            [['province_id', 'city_id', 'district_id', 'id'], 'integer'],
            [['address', 'name'], 'trim']
        ];
    }

    public function save(){
        if(!$this->validate()){
            return $this->responseErrorInfo();
        }
        try {

            if($this->id){
                $region = PerformDistributionRegion::findOne($this->id);
                if(!$region){
                    throw new \Exception("数据异常！区域信息不存在");
                }
            }else{
                $region = new PerformDistributionRegion([
                    "mall_id"    => \Yii::$app->mall->id,
                    "created_at" => time()
                ]);
            }
            $region->region_id   = $this->region_id;
            $region->updated_at  = time();
            $region->name        = $this->name;
            $region->is_delete   = 0;
            $region->province_id = $this->province_id;
            $region->city_id     = $this->city_id;
            $region->district_id = $this->district_id;
            $region->address     = $this->address;
            if(!$region->save()){
                throw new \Exception($this->responseErrorMsg($region));
            }

            return $this->returnApiResultData(ApiCode::CODE_SUCCESS);
        }catch (\Exception $e){
            return $this->returnApiResultData(ApiCode::CODE_FAIL, $e->getMessage());
        }
    }
}