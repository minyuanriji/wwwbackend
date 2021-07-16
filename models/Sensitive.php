<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%goods_service}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property int $mch_id
 * @property string $sensitive 敏感词
 * @property int $created_at
 * @property int $is_delete
 */
class Sensitive extends  BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%sensitive}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['sensitive'], 'required'],
            [['sensitive'], 'string'],
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
            'mch_id' => 'Mch ID',
            'sensitive' => '敏感词',
            'created_at' => 'Created At',
            'is_delete' => 'Is Delete',
        ];
    }
}
