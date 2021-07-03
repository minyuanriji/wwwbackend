<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%validate_code_log}}".
 *
 * @property int $id
 * @property string $target
 * @property string $content
 * @property int $updated_at
 * @property int $created_at
 * @property int $deleted_at
 * @property int $is_delete 是否删除
 */
class ValidateCodeLog extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%validate_code_log}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['target'], 'required'],
            [['updated_at', 'created_at', 'deleted_at', 'is_delete'], 'integer'],
            [['target'], 'string', 'max' => 11],
            [['content'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'target' => 'Target',
            'content' => 'Content',
            'updated_at' => 'Updated At',
            'created_at' => 'Created At',
            'deleted_at' => 'Deleted At',
            'is_delete' => '是否删除',
        ];
    }
}