<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%role_user}}".
 *
 * @property int $id
 * @property int $role_id
 * @property int $admin_id
 * @property int $is_delete
 */
class RoleUser extends BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%role_user}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['role_id', 'admin_id'], 'required'],
            [['role_id', 'admin_id', 'is_delete'], 'integer'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'role_id' => 'Role ID',
            'admin_id' => 'User ID',
            'is_delete' => 'Is Delete',
        ];
    }
}
