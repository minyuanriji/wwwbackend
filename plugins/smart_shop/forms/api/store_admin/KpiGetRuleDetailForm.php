<?php

namespace app\plugins\smart_shop\forms\api\store_admin;

use app\core\ApiCode;
use app\models\BaseModel;
use app\plugins\smart_shop\models\KpiSetting;

class KpiGetRuleDetailForm extends BaseModel{

    public $merchant_id;
    public $store_id;
    public $type;
    public $source_table;
    public $source_id;
    public $id;

    public function rules()
    {
        return [
            [['merchant_id', 'store_id', 'type', 'source_table'], 'required'],
            [['source_table', 'type'], 'trim'],
            [['source_id', 'id'], 'integer']
        ];
    }

    public function getDetail(){

        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }

        try {

            $where = [
                "mall_id"      => \Yii::$app->mall->id,
                "ss_mch_id"    => $this->merchant_id,
                "ss_store_id"  => $this->store_id,
                "type"         => $this->type,
                "source_table" => $this->source_table,
            ];
            if($this->id){
                $where['id'] = $this->id;
            }else{
                $where['source_id'] = $this->source_id;
            }

            $kpiSetting = KpiSetting::findOne($where);

            if($kpiSetting){
                $detail = $kpiSetting->getAttributes();
                $detail['value'] = $kpiSetting['value'] ? @json_decode($kpiSetting['value'], true) : '';
            }else{
                $detail = '';
            }

            return $this->returnApiResultData(ApiCode::CODE_SUCCESS, null, [
                "detail" => $detail
            ]);
        }catch (\Exception $e){
            return $this->returnApiResultData(ApiCode::CODE_FAIL, $e->getMessage());
        }
    }

}