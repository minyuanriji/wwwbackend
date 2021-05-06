<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%home_page}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property int $is_delete
 * @property string|null $page_data
 * @property int $created_at
 * @property int $deleted_at
 * @property int $updated_at
 */
class HomePage extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%home_page}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id'], 'required'],
            [['mall_id', 'is_delete', 'created_at', 'deleted_at', 'updated_at'], 'integer'],
            [['page_data'], 'string'],
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
            'is_delete' => 'Is Delete',
            'page_data' => 'Page Data',
            'created_at' => 'Created At',
            'deleted_at' => 'Deleted At',
            'updated_at' => 'Updated At',
        ];
    }
}
