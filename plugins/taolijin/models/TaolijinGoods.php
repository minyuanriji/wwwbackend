<?php
namespace app\plugins\taolijin\models;

use app\models\BaseActiveRecord;

class TaolijinGoods extends BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%plugin_taolijin_goods}}';
    }

    public function rules()
    {
        return [
            [['mall_id', 'deduct_integral', 'price', 'status', 'name', 'detail',
              'cover_pic', 'pic_url', 'video_url', 'unit', 'updated_at', 'created_at', 'ali_type',
              'ali_unique_id', 'gift_price'], 'required'],
            [['is_delete', 'ali_other_data'], 'safe']
        ];
    }
}

