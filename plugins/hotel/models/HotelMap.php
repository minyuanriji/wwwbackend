<?php
namespace app\plugins\hotel\models;


use app\models\BaseActiveRecord;

class HotelMap extends BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%plugin_hotel_map}}';
    }

    public function rules()
    {
        return [
            [['mall_id', 'hotel_id', 'longitude', 'latitude', 'type'], 'required'],
            [[], 'safe']
        ];
    }
}