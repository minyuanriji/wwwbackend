<?php
namespace app\models;


class EfpsRegionMcc extends BaseActiveRecord {

    /**
     * {@inheritdoc}
     */
    public static function tableName(){
        return '{{%efps_region_mcc}}';
    }

    public function rules(){
        return [
            [['code', 'parent', 'name', 'level'], 'required']
        ];
    }

}