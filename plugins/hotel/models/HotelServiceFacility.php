<?php
namespace app\plugins\hotel\models;


use app\models\BaseActiveRecord;

class HotelServiceFacility extends BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%hotel_service_facility}}';
    }

    public function rules()
    {
        return [
            [['mall_id', 'group', 'label', 'key'], 'required'],
            [[], 'safe']
        ];
    }
}
