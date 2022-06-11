<?php

namespace app\plugins\smart_shop\forms\api\store_admin;

use app\core\ApiCode;
use app\models\BaseModel;
use app\plugins\smart_shop\models\KpiSetting;

class KpiSaveRuleForm extends BaseModel{

    public $merchant_id;
    public $store_id;
    public $type;
    public $source_table;
    public $source_id;
    public $value;

    public function rules() {
        return [
            [['merchant_id', 'store_id', 'type', 'source_table', 'source_id', 'value'], 'required'],
            [['value', 'source_table', 'type'], 'trim'],
            [['source_id'], 'integer']
        ];
    }

    public function save(){
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }

        try {

            $uniqueData = [
                "mall_id"      => \Yii::$app->mall->id,
                "ss_mch_id"    => $this->merchant_id,
                "ss_store_id"  => $this->store_id,
                "type"         => $this->type,
                "source_table" => $this->source_table,
                "source_id"    => $this->source_id
            ];
            $kpiSetting = KpiSetting::findOne($uniqueData);
            if(!$kpiSetting){
                $kpiSetting = new KpiSetting(array_merge($uniqueData, [
                    "created_at" => time()
                ]));
            }
            $kpiSetting->updated_at = time();
            $kpiSetting->value      = $this->value;
            $kpiSetting->is_delete  = 0;
            if(!$kpiSetting->save()){
                throw new \Exception($this->responseErrorMsg($kpiSetting));
            }

            return $this->returnApiResultData(ApiCode::CODE_SUCCESS);
        }catch (\Exception $e){
            return $this->returnApiResultData(ApiCode::CODE_FAIL, $e->getMessage());
        }
    }
}