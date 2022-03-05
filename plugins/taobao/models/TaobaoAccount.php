<?php

namespace app\plugins\taobao\models;

use app\models\BaseActiveRecord;

class TaobaoAccount extends BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%plugin_taobao_account}}';
    }

    public function rules()
    {
        return [
            [['mall_id', 'created_at', 'updated_at', 'app_key', 'app_secret', 'adzone_id', 'special_id', 'invite_code'], 'required'],
            [['is_delete'], 'safe']
        ];
    }
}
