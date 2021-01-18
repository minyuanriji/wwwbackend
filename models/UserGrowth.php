<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%user_growth}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property int $user_id
 * @property string $keyword 关键字
 * @property float $value 数值
 * @property int $created_at
 * @property int $is_delete
 * @property int $updated_at
 * @property int $deleted_at
 */



class UserGrowth extends BaseActiveRecord
{

    const KEY_DISTRIBUTION_ORDER_PRICE = 'distribution_order_price';//分销订单金额
    const KEY_DISTRIBUTION_ORDER_COUNT = 'distribution_order_count';//分销订单数量
    const KEY_DISTRIBUTION_FIRST_PRICE = 'distribution_first_price';//一级分销订单金额
    const KEY_DISTRIBUTION_FIRST_COUNT = 'distribution_first_count';//一级分销数量
    const KEY_SELF_BUY_ORDER_PRICE = 'self_buy_order_price';//自购订单金额
    const KEY_SELF_BUY_ORDER_COUNT = 'self_buy_order_count';//自购订单数量
    const KEY_TEAM_USER_COUNT = 'team_user_count';//团队总人数
    const KEY_TEAM_USER_FIRST_COUNT = 'team_user_first_count';//一级团队总人数
    const KEY_TEAM_USER_DISTRIBUTION_COUNT = 'team_user_distribution_count';//团队粉丝中分销商总数
    const KEY_TEAM_USER_DISTRIBUTION_FIRST_COUNT = 'team_user_distribution_first_count';//一级分销商总人数
    const KEY_TOTAL_PRICE = 'total_price';
    const KEY_DISTRIBUTION_COUNT = 'distribution_count';//累计收益

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%user_growth}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'user_id', 'keyword'], 'required'],
            [['mall_id', 'user_id', 'created_at', 'is_delete', 'updated_at', 'deleted_at'], 'integer'],
            [['value'], 'number'],
            [['keyword'], 'string', 'max' => 45],
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
            'keyword' => '关键字',
            'value' => '数值',
            'created_at' => 'Created At',
            'is_delete' => 'Is Delete',
            'updated_at' => 'Updated At',
            'deleted_at' => 'Deleted At',
        ];
    }
}
