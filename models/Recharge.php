<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%recharge}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property string $name
 * @property string $pay_price 支付价格
 * @property string $give_money 赠送价格
 * @property int $is_delete 删除
 * @property int $created_at
 * @property int $updated_at
 * @property int $deleted_at
 * @property int $give_score 赠送的积分
 */
class Recharge extends BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%recharge}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'name', 'pay_price', 'created_at', 'updated_at', 'deleted_at'], 'required'],
            [['mall_id', 'is_delete', 'give_score'], 'integer'],
            [['pay_price', 'give_money'], 'number'],
            [['created_at', 'updated_at', 'deleted_at'], 'safe'],
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
            'pay_price' => '支付价格',
            'give_money' => '赠送价格',
            'is_delete' => '删除',
            'created_at' => 'Created At',
            'updated_at' => 'Update At',
            'deleted_at' => 'Deleted Time',
            'give_score' => '赠送的积分',
        ];
    }
}
