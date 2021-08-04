<?php

namespace app\plugins\giftpacks\models;


use app\models\BaseActiveRecord;

class GiftpacksItem extends BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%plugin_giftpacks_item}}';
    }

    public function rules()
    {
        return [
            [['mall_id', 'name', 'cover_pic', 'item_price', 'pack_id', 'store_id', 'goods_id', 'updated_at', 'created_at'], 'required'],
            [['expired_at', 'max_stock', 'usable_times', 'is_delete'], 'safe']
        ];
    }
}


