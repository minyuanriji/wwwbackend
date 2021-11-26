<?php

namespace app\plugins\mch\forms\common\apply;


use app\core\ApiCode;
use app\helpers\CityHelper;
use app\models\BaseModel;
use app\plugins\mch\models\Mch;
use app\plugins\mch\models\MchApply;

class MchApplyInfoForm extends BaseModel{

    public $user_id;

    public function rules(){
        return [
            [['user_id'], 'required'],
            [['user_id'], 'integer'],
        ];
    }

    public function get(){
        if(!$this->validate()){
            return $this->responseErrorInfo();
        }

        try {
            $applyModel = MchApply::findOne([
                "user_id" => $this->user_id
            ]);
            if($applyModel && $applyModel->status == "passed"){
                $mch = Mch::findOne(["user_id" => $this->user_id]);
                if(!$mch || $mch->is_delete || $mch->review_status != Mch::REVIEW_STATUS_CHECKED){
                    $applyModel->status = "applying'";
                    $applyModel->updated_at = time();
                    $applyModel->save();
                }
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'data' => $this->getData($applyModel)
            ];
        }catch (\Exception $e){
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg' => $e->getMessage()
            ];
        }
    }
    
    private function getData($applyModel){
        $info = [];

        if($applyModel){
            $info['realname']    = $applyModel->realname;
            $info['mobile']      = $applyModel->mobile;
            $info['status']      = $applyModel->status;
            $info['apply_time']  = date("Y-m-d H:i:s", $applyModel->created_at);
            $info['update_time'] = date("Y-m-d H:i:s", $applyModel->updated_at);
            $info['remark']      = $applyModel->remark;
            $info = array_merge($info, json_decode($applyModel->json_apply_data, true));
        }

        $info['realname'] = isset($info['realname']) ? $info['realname'] : "";
        $info['mobile'] = isset($info['mobile']) ? $info['mobile'] : "";
        $info['status'] = isset($info['status']) ? $info['status'] : "applying";
        $info['apply_time'] = isset($info['apply_time']) ? $info['apply_time'] : "";
        $info['update_time'] = isset($info['update_time']) ? $info['update_time'] : "";
        $info['remark'] = isset($info['remark']) ? $info['remark'] : "";
        $info['store_name'] = isset($info['store_name']) ? $info['store_name'] : "";
        $info['store_mch_common_cat_id'] = isset($info['store_mch_common_cat_id']) ? $info['store_mch_common_cat_id'] : 0;
        $info['store_province_id'] = isset($info['store_province_id']) ? $info['store_province_id'] : 0;
        $info['store_city_id'] = isset($info['store_city_id']) ? $info['store_city_id'] : 0;
        $info['store_district_id'] = isset($info['store_district_id']) ? $info['store_district_id'] : 0;
        $info['store_address'] = isset($info['store_address']) ? $info['store_address'] : "";
        $info['store_longitude'] = isset($info['store_longitude']) ? $info['store_longitude'] : "";
        $info['store_latitude'] = isset($info['store_latitude']) ? $info['store_latitude'] : "";
        $info['license_num'] = isset($info['license_num']) ? $info['license_num'] : "";
        $info['license_name'] = isset($info['license_name']) ? $info['license_name'] : "";
        $info['license_pic'] = isset($info['license_pic']) ? $info['license_pic'] : "";
        $info['cor_num'] = isset($info['cor_num']) ? $info['cor_num'] : "";
        $info['cor_pic1'] = isset($info['cor_pic1']) ? $info['cor_pic1'] : "";
        $info['cor_pic2'] = isset($info['cor_pic2']) ? $info['cor_pic2'] : "";
        $info['cor_realname'] = isset($info['cor_realname']) ? $info['cor_realname'] : "";
        $info['settle_bank'] = isset($info['settle_bank']) ? $info['settle_bank'] : "";
        $info['settle_num'] = isset($info['settle_num']) ? $info['settle_num'] : "";
        $info['settle_realname'] = isset($info['settle_realname']) ? $info['settle_realname'] : "";
        $info['is_special_discount'] = isset($info['is_special_discount']) ? $info['is_special_discount'] : 0;
        $info['settle_special_rate_remark'] = isset($info['settle_special_rate_remark']) ? $info['settle_special_rate_remark'] : "";
        $info['settle_discount'] = isset($info['settle_discount']) ? (float)$info['settle_discount'] : "";

        if ($info['store_province_id'] && $info['store_city_id'] && $info['store_district_id']) {
            $cityInfo = CityHelper::reverseData($info['store_district_id'], $info['store_city_id'], $info['store_province_id']);
        }
        $info['province_name'] = $cityInfo['province']['name'] ?? '';
        $info['city_name'] = $cityInfo['city']['name'] ?? '';
        $info['district_name'] = $cityInfo['district']['name'] ?? '';

        return $info;
    }
}