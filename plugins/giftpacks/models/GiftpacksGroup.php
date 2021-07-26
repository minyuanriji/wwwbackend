<?php

namespace app\plugins\giftpacks\models;


use app\models\BaseActiveRecord;

class GiftpacksGroup extends BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%plugin_giftpacks_group}}';
    }

    public function rules()
    {
        return [
            [['mall_id', 'pack_id', 'user_id', 'need_num', 'created_at', 'updated_at'], 'required'],
            [['user_num', 'status', 'expired_at'], 'safe']
        ];
    }
}


