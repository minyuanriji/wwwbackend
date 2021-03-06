<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%income_log}}".
 *
 * @property int $id
 * @property int $user_id
 * @property int $type 类型：0=未知,1=收入，2=支出
 * @property string $money 变动金额
 * @property string $income 当前收益
 * @property string $desc 变动说明
 * @property int $flag 0冻结1结算2退款3提现
 * @property int $from 来源1分销
 * @property int $created_at
 * @property int $updated_at
 * @property string $mall_id
 * @property int $order_detail_id 订单详情id
 * @property int $deleted_at 删除时间
 * @property int $is_delete 是否删除
 * @property User $user
 * @property OrderDetail $orderDetail
 */
class IncomeLog extends BaseActiveRecord
{
    /** @var int 类型1收入2支出 */
    const TYPE_IN = 1;
    const TYPE_OUT = 2;

    const FLAG_FROZEN = 0;
    const FLAG_SETTLEMENT = 1;
    const FLAG_REFUND = 2;
    const FLAG_CASH = 3;

    const EVENT_INCOME_CHANGE='income_change';

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%income_log}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id', 'mall_id', 'type', 'money','income', 'desc', 'from', 'created_at'], 'required'],
            [['user_id', 'mall_id', 'type','from','flag','order_detail_id','deleted_at','is_delete'], 'integer'],
            [['money','income'], 'number'],
            [['desc'], 'string'],
            [['created_at','updated_at'], 'safe'],
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
            'order_detail_id' => '订单详情id',
            'type' => '类型：1=收入，2=支出',
            'money' => '变动金额',
            'income' => '当前余额',
            'desc' => '变动说明',
            'from' => '来源1分销',
            'flag' => '0冻结1结算',
            'created_at' => 'Created At',
            'updated_at' => 'updated_at',
            'deleted_at'=>'deleted_at',
            'is_delete'=>'is_delete'
        ];
    }

    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }

    public function getOrderDetail()
    {
        return $this->hasOne(OrderDetail::className(), ['id' => 'order_detail_id']);
    }
}
