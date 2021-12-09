<?php

namespace app\plugins\taolijin\models;

use app\models\BaseActiveRecord;
use app\models\User;

class TaolijinUserAliBind extends BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%plugin_taolijin_user_ali_bind}}';
    }

    public function rules()
    {
        return [
            [['mall_id', 'ali_id', 'special_id', 'user_id', 'invite_code', 'created_at', 'updated_at'], 'required'],
            [[], 'safe']
        ];
    }

    /**
     * 获取用户
     * @return \yii\db\ActiveQuery
     */
    public function getUser(){
        return $this->hasOne(User::class, ["id" => "user_id"]);
    }
}
