<?php

namespace app\plugins\giftpacks\models;


use app\models\BaseActiveRecord;

class GiftpacksGroupPackItem extends BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%plugin_giftpacks_group_pack_item}}';
    }

    public function rules()
    {
        return [
            [['mall_id', 'group_id', 'pack_item_id', 'user_id'], 'required'],
            [[], 'safe']
        ];
    }
}




