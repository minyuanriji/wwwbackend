<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%role_permission}}".
 *
 * @property int $id
 * @property int $role_id
 * @property string $permissions
 * @property int $is_delete
 */
class RolePermission extends BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%role_permission}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['role_id', 'permissions'], 'required'],
            [['role_id', 'is_delete'], 'integer'],
            [['permissions'], 'string'],
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
            'permissions' => 'Permissions',
            'is_delete' => 'Is Delete',
        ];
    }
}
