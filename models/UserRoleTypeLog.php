<?php
namespace app\models;


class UserRoleTypeLog extends BaseActiveRecord
{

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%user_role_type_log}}';
    }

    public function rules()
    {
        return [
            [['mall_id', 'origin_type', 'target_type', 'user_id', 'source_id', 'source_type', 'created_at'], 'required'],
            [['content'], 'safe']
        ];
    }
}