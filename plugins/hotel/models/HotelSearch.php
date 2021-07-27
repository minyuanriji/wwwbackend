<?php
namespace app\plugins\hotel\models;


use app\models\BaseActiveRecord;

class HotelSearch extends BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%plugin_hotel_searchs}}';
    }

    public function rules()
    {
        return [
            [['mall_id', 'search_id', 'created_at', 'updated_at'], 'required'],
            [['content'], 'safe']
        ];
    }
}

