<?php

namespace app\plugins\mch\models;

use app\models\BaseActiveRecord;

class MchAdminUser extends BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%plugin_mch_admin_user}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'mch_id', 'auth_key', 'access_token', 'created_at', 'updated_at'], 'required'],
            [['login_ip', 'last_login_at'], 'safe']
        ];
    }
}