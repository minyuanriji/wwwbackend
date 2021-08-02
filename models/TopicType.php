<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%topic_type}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property string $name 名称
 * @property int $sort 排序
 * @property int $status 状态
 * @property int $is_delete 删除
 * @property int $created_at
 * @property int $deleted_at
 * @property int $updated_at
 * @property Topic[] $topics
 */
class TopicType extends BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%topic_type}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'name', 'created_at', 'deleted_at', 'updated_at'], 'required'],
            [['mall_id', 'sort', 'is_delete', 'status'], 'integer'],
            [['created_at', 'deleted_at', 'updated_at'], 'safe'],
            [['name'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'mall_id' => 'mall ID',
            'name' => '名称',
            'sort' => '排序',
            'status' => '状态',
            'is_delete' => '删除',
            'created_at' => 'Created At',
            'deleted_at' => 'Deleted Time',
            'updated_at' => 'Updated At',
        ];
    }

    public function getTopics()
    {
        return $this->hasMany(Topic::className(), ['type' => 'id']);
    }
}
