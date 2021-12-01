<?php

namespace app\plugins\taolijin\models;

use app\models\BaseActiveRecord;

class TaolijinUserAuth extends BaseActiveRecord
{

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%plugin_taolijin_user_auth}}';
    }

    public function rules()
    {
        return [
            [['mall_id', 'ali_id', 'user_id', 'updated_at', 'created_at', 'refresh_token_expire_at', 'refresh_token', 'access_token_expire_at', 'access_token'], 'required'],
            [['extra_json_data'], 'safe']
        ];
    }
}