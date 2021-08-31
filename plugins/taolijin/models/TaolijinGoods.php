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
            [['mall_id', 'deduct_integral', 'price', 'status', 'name',
              'cover_pic', 'pic_url', 'unit', 'updated_at', 'created_at', 'ali_type',
              'ali_unique_id', 'ali_rate', 'gift_price'], 'required'],
            [['is_delete', 'ali_other_data', 'video_url', 'detail', 'ali_url'], 'safe']
        ];
    }
}

