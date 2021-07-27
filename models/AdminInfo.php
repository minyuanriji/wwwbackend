<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%admin_info}}".
 *
 * @property int $id
 * @property int $admin_id
 * @property string $permissions 账户权限
 * @property string $remark 备注
 * @property string $is_delete 是否删除
 * @property int $is_default 是否使用默认权限
 * @property string $secondary_permissions 二级权限
 * @property Admin $admin
 */
class AdminInfo extends BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%admin_info}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['admin_id', 'permissions'], 'required'],
            [['admin_id', 'is_delete', 'is_default'], 'integer'],
            [['permissions', 'secondary_permissions'], 'string'],
            [['remark'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'admin_id' => '用户 ID',
            'permissions' => '用户权限',
            'remark' => 'Remark',
            'is_delete' => 'Is Delete',
            'is_default' => '是否使用默认权限',
            'secondary_permissions' => '二级权限',
        ];
    }

    public function getAdmin()
    {
        return $this->hasOne(User::class, ['id' => 'admin_id']);
    }

}
