<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%topic_favorite}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property int $user_id
 * @property int $topic_id
 * @property int $is_delete
 * @property int $deleted_at
 * @property int $created_at
 * @property int $updated_at
 */
class TopicFavorite extends BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%topic_favorite}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'user_id', 'topic_id', 'deleted_at', 'created_at', 'updated_at'], 'required'],
            [['mall_id', 'user_id', 'topic_id', 'is_delete'], 'integer'],
            [['deleted_at', 'created_at', 'updated_at'], 'safe'],
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
            'topic_id' => 'Topic ID',
            'is_delete' => 'Is Delete',
            'deleted_at' => 'Deleted Time',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }
}
