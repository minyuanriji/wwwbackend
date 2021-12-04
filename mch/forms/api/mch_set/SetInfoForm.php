<?php
namespace app\mch\forms\api\mch_set;


use app\core\ApiCode;
use app\helpers\ArrayHelper;
use app\models\BaseModel;
use app\models\DistrictData;
use app\models\Store;
use app\plugins\mch\models\Mch;

class SetInfoForm extends BaseModel{

    public $mch_id;
    public $cover_url;
    public $name;
    public $province_id;
    public $city_id;
    public $district_id;
    public $longitude;
    public $latitude;
    public $address;
    public $store_mch_common_cat_id;
    public $description;
    public $start_business_time;
    public $end_business_time;

    public function rules(){
        return array_merge(parent::rules(), [
            [['mch_id', 'name', 'cover_url', 'province_id', 'city_id', 'longitude', 'latitude', 'address', 'store_mch_common_cat_id'], 'required'],
            [['mch_id', 'province_id', 'city_id', 'store_mch_common_cat_id'], 'integer'],
            [['cover_url', 'name', 'longitude', 'latitude', 'address', 'description'], 'string'],
            [['district_id', 'start_business_time', 'end_business_time'], 'safe'],
            ['description', 'checkDetail']
        ]);
    }

    public function checkDetail($attribute, $params)
    {
        $detail = $this->description;
        if (strlen($detail) > 1000) {
            $this->addError($attribute, "店铺介绍最大字节不能大于255！");
        }
    }

    public function save(){

        if(!$this->validate()){
            return $this->responseErrorInfo();
        }

        $t = \Yii::$app->db->beginTransaction();
        try {
            $mchStore = Store::findOne([
                "mch_id" => $this->mch_id
            ]);
            if(!$mchStore || $mchStore->is_delete){
                throw new \Exception("无法获取店铺信息");
            }

            $this->checkDistrict();

            $pattern = "/\d+\.\d+/";
            if(!preg_match($pattern, $this->latitude) || !preg_match($pattern, $this->longitude)){
                throw new \Exception("经纬度坐标格式有误");
            }

            $mchStore->cover_url            = $this->cover_url;
            $mchStore->name                 = $this->name;
            $mchStore->province_id          = $this->province_id;
            $mchStore->city_id              = $this->city_id;
            $mchStore->district_id          = $this->district_id;
            $mchStore->longitude            = $this->longitude;
            $mchStore->latitude             = $this->latitude;
            $mchStore->address              = $this->address;
            $mchStore->description          = $this->description;
            $mchStore->business_hours       = $this->start_business_time . '-' . $this->end_business_time;
            if(!$mchStore->save()){
                throw new \Exception($this->responseErrorMsg($mchStore));
            }

            $detail = ArrayHelper::toArray($mchStore);
            $detail['pic_url'] = json_decode($detail['pic_url'], true);
            if(isset($detail['pic_url'][0]) && empty($detail['pic_url'][0])){
                unset($detail['pic_url'][0]);
            }

            $mch = Mch::findOne($this->mch_id);
            if (!$mch || $mch->is_delete == 1)
                throw new \Exception('商户不存在或已删除！');

            $mch->mch_common_cat_id = $this->store_mch_common_cat_id;
            if(!$mch->save())
                throw new \Exception($this->responseErrorMsg($mch));

            $t->commit();
            return $this->returnApiResultData(ApiCode::CODE_SUCCESS, '', ['detail' => $detail]);
        }catch (\Exception $e){
            $t->rollBack();
            return $this->returnApiResultData(ApiCode::CODE_FAIL, $e->getMessage());
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