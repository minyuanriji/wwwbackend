<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%member_benefit}}".
 *
 * @property int $id
 * @property int $level_id
 * @property string $title
 * @property string $content
 * @property string $pic_url
 * @property int $is_delete
 * @property int $created_at
 * @property int $deleted_at
 * @property int $updated_at
 */
class MemberBenefit extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%member_benefit}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['level_id'], 'required'],
            [['level_id', 'is_delete', 'created_at', 'deleted_at', 'updated_at'], 'integer'],
            [['title'], 'string', 'max' => 65],
            [['content', 'pic_url'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'level_id' => 'Level ID',
            'title' => 'Title',
            'content' => 'Content',
            'pic_url' => 'Pic Url',
            'is_delete' => 'Is Delete',
            'created_at' => 'Created At',
            'deleted_at' => 'Deleted At',
            'updated_at' => 'Updated At',
        ];
    }
}
