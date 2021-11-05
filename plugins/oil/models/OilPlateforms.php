<?php

namespace app\plugins\oil\models;

use app\models\BaseActiveRecord;

class OilPlateforms extends BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%plugin_oil_plateforms}}';
    }

    public function rules()
    {
        return [
            [['mall_id', 'name', 'sdk_src', 'class_dir', 'created_at', 'updated_at'], 'required'],
            [['is_delete', 'region_deny', 'is_enabled', 'product_json_data'], 'safe']
        ];
    }

    public function getParams(){
        $paramArray = @json_decode($this->json_param, true);
        $data = [];
        if($paramArray){
            foreach($paramArray as $item){
                $data[$item['name']] = $item['value'];
            }
        }
        return $data;
    }
}