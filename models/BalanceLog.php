<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%balance_log}}".
 *
 * @property int $id
 * @property int $user_id
 * @property int $type 类型：0=未知,1=收入，2=支出
 * @property string $money 变动金额
 * @property string $balance 当前余额
 * @property string $desc 变动说明
 * @property string $custom_desc 自定义详细说明
 * @property int $created_at
 * @property string $mall_id
 * @property string source_type
 * @property int source_id
 */
class BalanceLog extends BaseActiveRecord
{
    const TYPE_ADD = 1;
    const TYPE_SUB = 2;

    const EVENT_BALANCE_CHANGE = "balance_change"; //余额变动事件;
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%balance_log}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id', 'mall_id', 'type', 'money','balance', 'desc', 'created_at'], 'required'],
            [['user_id', 'mall_id', 'type'], 'integer'],
            [['money','balance'], 'number'],
            [['desc', 'custom_desc', 'custom_desc'], 'string'],
            [['created_at', 'source_type', 'source_id'], 'safe'],
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
            'type' => '类型：1=收入，2=支出',
            'money' => '变动金额',
            'balance' => '当前余额',
            'desc' => '变动说明',
            'source_type' => '来源类型',
            'source_id' => '来源表ID',
            'custom_desc' => '自定义详细说明',
            'created_at' => 'Created At',
        ];
    }

    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }
}
