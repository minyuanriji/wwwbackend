<?php
namespace app\plugins\hotel\models;


use app\models\BaseActiveRecord;

class HotelRoom extends BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%plugin_hotel_room}}';
    }

    public function rules()
    {
        return [
            [['mall_id', 'hotel_id', 'product_code', 'bed_type', 'created_at', 'updated_at'], 'required'],
            [['is_delete', 'bed_width', 'floor', 'room_size', 'window',
              'people_num', 'policy_ban_smoking', 'policy_add_bed', 'name',
              'policy_breakfast', 'json_service_facilitys', 'max_room'], 'safe']
        ];
    }

}




