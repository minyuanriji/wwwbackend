<?php

namespace app\plugins\alibaba\models;

use app\models\BaseActiveRecord;

class AlibabaApp extends BaseActiveRecord{

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%plugin_alibaba_app}}';
    }

    public function rules(){
        return [
            [['mall_id', 'name', 'app_key', 'secret', 'created_at', 'updated_at', 'type'], 'required'],
            [['access_token', 'token_expired_at', 'refresh_token', 'refresh_expired_at', 'token_info', 'is_delete'], 'safe']
        ];
    }

}