<?php
namespace app\plugins\hotel\models;


use app\models\BaseActiveRecord;

class HotelPics extends BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%plugin_hotel_pics}}';
    }

    public function rules()
    {
        return [
            [['mall_id', 'hotel_id', 'created_at'], 'required'],
            [['room_product_code' , 'pic_url', 'descript'], 'safe']
        ];
    }
}






