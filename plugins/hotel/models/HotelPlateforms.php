<?php
namespace app\plugins\hotel\models;


use app\models\BaseActiveRecord;

class HotelPlateforms extends BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%plugin_hotel_plateforms}}';
    }

    public function rules()
    {
        return [
            [['mall_id', 'type', 'source_code', 'plateform_code', 'plateform_class', 'plateform_json_data'], 'required'],
            [[], 'safe']
        ];
    }
}



