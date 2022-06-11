<?php

namespace app\plugins\smart_shop\forms\api\store_admin;

use app\core\ApiCode;
use app\models\BaseModel;
use app\plugins\smart_shop\models\KpiSetting;

class KpiGetRuleListForm extends BaseModel{

    public $merchant_id;
    public $store_id;
    public $type;
    public $source_table;
    public $page;

    public function rules(){
        return [
            [['merchant_id', 'store_id', 'type', 'source_table'], 'required'],
            [['source_table', 'type'], 'trim'],
            [['page'], 'integer']
        ];
    }

    public function getList(){
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }

        try {
            $query = KpiSetting::find()->where([
                "mall_id"      => \Yii::$app->mall->id,
                "ss_mch_id"    => $this->merchant_id,
                "ss_store_id"  => $this->store_id,
                "type"         => $this->type,
                "source_table" => $this->source_table,
                "is_delete"    => 0
            ])->orderBy("id DESC");

            $list = $query->asArray()->page($pagination, 10, $this->page)->all();
            if($list){
                foreach($list as $key => $row){
                    $list[$key]['value'] = $row['value'] ? @json_decode($row['value'], true) : '';
                }
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'data' => [
                    'list'       => $list ? $list : [],
                    'pagination' => $pagination,
                ]
            ];
        }catch (\Exception $e){
            return $this->returnApiResultData(ApiCode::CODE_FAIL, $e->getMessage());
        }
    }

}