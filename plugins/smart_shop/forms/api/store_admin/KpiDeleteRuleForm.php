<?php

namespace app\plugins\smart_shop\forms\api\store_admin;

use app\core\ApiCode;
use app\models\BaseModel;
use app\plugins\smart_shop\models\KpiSetting;

class KpiDeleteRuleForm extends BaseModel{

    public $merchant_id;
    public $store_id;
    public $type;
    public $source_table;
    public $source_id;
    public $id;

    public function rules(){
        return [
            [['merchant_id', 'store_id', 'type', 'source_table'], 'required'],
            [['source_table', 'type'], 'trim'],
            [['source_id', 'id'], 'integer']
        ];
    }

    public function delete(){
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }

        try {

            $kpiSetting = KpiSetting::findOne([
                "mall_id"      => \Yii::$app->mall->id,
                "ss_mch_id"    => $this->merchant_id,
                "ss_store_id"  => $this->store_id,
                "type"         => $this->type,
                "source_table" => $this->source_table,
                "id"           => $this->id
            ]);
            if(!$kpiSetting){
                throw new \Exception("信息不存在");
            }

            $kpiSetting->is_delete = 1;
            if(!$kpiSetting->save()){
                throw new \Exception(json_encode($kpiSetting->getErrors()));
            }

            return $this->returnApiResultData(ApiCode::CODE_SUCCESS);
        }catch (\Exception $e){
            return $this->returnApiResultData(ApiCode::CODE_FAIL, $e->getMessage());
        }
    }

}