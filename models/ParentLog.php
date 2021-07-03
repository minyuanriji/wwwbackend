<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%parent_log}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property int $user_id
 * @property int $before_parent_id
 * @property int $updated_at
 * @property int $created_at
 * @property int $deleted_at
 * @property int $is_delete
 * @property int $after_parent_id 变更之后的父级
 */
class ParentLog extends BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%parent_log}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'user_id'], 'required'],
            [['mall_id', 'user_id', 'before_parent_id', 'updated_at', 'created_at', 'deleted_at', 'is_delete', 'after_parent_id'], 'integer'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'mall_id' => 'Mall ID',
            'user_id' => 'User ID',
            'before_parent_id' => 'Before Parent ID',
            'updated_at' => 'Updated At',
            'created_at' => 'Created At',
            'deleted_at' => 'Deleted At',
            'is_delete' => 'Is Delete',
            'after_parent_id' => '变更之后的父级',
        ];
    }
}