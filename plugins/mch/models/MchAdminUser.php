<?php

namespace app\plugins\mch\models;

use app\models\BaseActiveRecord;
use app\models\Store;

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
            [['token_expired_at', 'login_ip', 'last_login_at'], 'safe']
        ];
    }

    /**
     * 获取商户
     * @return \yii\db\ActiveQuery
     */
    public function getMch(){
        return $this->hasOne(Mch::class, ["id" => "mch_id"]);
    }

    /**
     * 获取商户
     * @return \yii\db\ActiveQuery
     */
    public function getStore(){
        return $this->hasOne(Store::class, ["mch_id" => "mch_id"]);
    }
}