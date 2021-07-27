<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%file_path}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property string $url
 * @property string $type image、doc、video
 * @property string $name
 * @property int $is_delete
 * @property int $created_at
 * @property int $group_id
 * @property string|null $thumb_url
 */
class FilePath extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%file_path}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'url', 'type', 'name', 'created_at'], 'required'],
            [['mall_id', 'is_delete', 'created_at', 'group_id'], 'integer'],
            [['url', 'thumb_url'], 'string', 'max' => 2048],
            [['type'], 'string', 'max' => 8],
            [['name'], 'string', 'max' => 128],
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
            'url' => 'Url',
            'type' => 'Type',
            'name' => 'Name',
            'is_delete' => 'Is Delete',
            'created_at' => 'created_at',
            'group_id' => 'Group ID',
            'thumb_url' => 'Thumb Url',
        ];
    }
}
