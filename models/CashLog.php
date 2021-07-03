<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%cash_log}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property int $user_id
 * @property int $type 类型 1--收入 2--支出
 * @property float $price 变动佣金
 * @property string|null $desc
 * @property string|null $custom_desc
 * @property int $is_delete
 * @property int $created_at 创建时间
 * @property int $deleted_at 删除时间
 * @property int $updated_at 修改时间
 */
class CashLog extends BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%cash_log}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'user_id'], 'required'],
            [['mall_id', 'user_id', 'type', 'is_delete', 'created_at', 'deleted_at', 'updated_at'], 'integer'],
            [['price'], 'number'],
            [['desc', 'custom_desc'], 'string'],
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
            'type' => '类型 1--收入 2--支出',
            'price' => '变动佣金',
            'desc' => 'Desc',
            'custom_desc' => 'Custom Desc',
            'is_delete' => 'Is Delete',
            'created_at' => '创建时间',
            'deleted_at' => '删除时间',
            'updated_at' => '修改时间',
        ];
    }
}
