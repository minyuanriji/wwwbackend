<?php

namespace app\plugins\giftpacks\models;


use app\models\BaseActiveRecord;

class GiftpacksOrderItem extends BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%giftpacks_order_item}}';
    }

    public function rules()
    {
        return [
            [['mall_id', 'order_id', 'pack_item_id'], 'required'],
            [['current_num', 'max_num', 'expired_at'], 'safe']
        ];
    }
}






