<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%label}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property string $title 标题
 * @property string $sub_title 小标题
 * @property int $created_at
 * @property int $updated_at
 * @property int $deleted_at
 * @property int $is_delete
 * @property int $sort
 */
class Label extends BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%label}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'title', 'sub_title'], 'required'],
            [['mall_id', 'created_at', 'updated_at', 'deleted_at', 'is_delete','sort'], 'integer'],
            [['title', 'sub_title'], 'string', 'max' => 8],
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
            'title' => '标题',
            'sub_title' => '小标题',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'deleted_at' => 'Deleted At',
            'is_delete' => 'Is Delete',
            'sort'=>'sort'
        ];
    }
}
