<?php

namespace app\plugins\mch\forms\api;

use app\core\ApiCode;
use app\models\BaseModel;
use app\plugins\mch\models\MchApply;

class MchApplyEasyForm extends BaseModel{

    public $name;
    public $realname;
    public $mobile;
    public $province;
    public $city;
    public $district;
    public $province_id;
    public $city_id;
    public $district_id;
    public $zk;
    public $address;

    public function rules(){
        return [
            [['name', 'realname', 'mobile', 'province', 'city', 'province_id', 'city_id'], 'required'],
            [['name', 'realname', 'mobile', 'district', 'address'], 'trim'],
            [['district_id', 'zk'], 'safe'],
        ];
    }

    public function save(){
        if(!$this->validate()){
            return $this->responseErrorInfo();
        }
        try {

            $applyModel = MchApply::findOne([
                "user_id" => \Yii::$app->user->id
            ]);
            if(!$applyModel){
                $applyModel = new MchApply([
                    "mall_id"    => \Yii::$app->mall->id,
                    "user_id"    => \Yii::$app->user->id,
                    "status"     => "applying",
                    "created_at" => time()
                ]);
            }

            if($applyModel->status != "applying"){
                throw new \Exception("您已有门店入驻信息审核中，请耐心等候下");
            }

            if($applyModel->status == "passed"){
                throw new \Exception("一个用户只允许申请一家门店");
            }

            $applyModel->mobile     = $this->mobile;
            $applyModel->realname   = $this->realname;
            $applyModel->updated_at = time();
            $applyModel->status     = "verifying";
            $applyModel->json_apply_data = json_encode([
                "store_name"              => $this->name,
                "store_mch_common_cat_id" => 0,
                "store_province"          => $this->province,
                "store_province_id"       => $this->province_id,
                "store_city"              => $this->city,
                "store_city_id"           => $this->city_id,
                "store_district"          => $this->district,
                "store_district_id"       => $this->district_id,
                "store_address"           => $this->address,
                "settle_discount"         => intval($this->zk),
                "store_longitude"         => '',
                "store_latitude"          => '',
                "license_num"             => '',
                "license_name"            => '',
                "license_pic"             => '',
                "cor_num"                 => '',
                "cor_pic1"                => '',
                "cor_pic2"                => '',
                "cor_realname"            => '',
                "settle_bank"             => '',
                "settle_num"              => '',
                "settle_realname"         => ''
            ]);
            if(!$applyModel->save()){
                throw new \Exception($this->responseErrorMsg($applyModel));
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg'  => '保存成功'
            ];
        }catch (\Exception $e){
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg'  => $e->getMessage()
            ];
        }
    }
}