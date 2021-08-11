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
 * @property int $created_at
 * @property int $updated_at
 * @property string $mall_id
 * @property int $deleted_at 删除时间
 * @property int $is_delete 是否删除
 * @property int $is_manual 是否是后台手动充值
 * @property int $source_type 'admin管理员操作，cash提现，checkout结账单收入，store推荐门店分佣，migrate旧商城迁移，boss股东分红，goods商品分佣'
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
            [['user_id', 'mall_id', 'type', 'money','income', 'desc', 'created_at'], 'required'],
            [['user_id', 'mall_id', 'type','flag','deleted_at','is_delete','is_manual'], 'integer'],
            [['money','income', 'source_id'], 'number'],
            [['desc', 'source_type'], 'string'],
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
            'type' => '类型：1=收入，2=支出',
            'money' => '变动金额',
            'income' => '当前余额',
            'desc' => '变动说明',
            'flag' => '0冻结1结算',
            'created_at' => 'Created At',
            'updated_at' => 'updated_at',
            'deleted_at'=>'deleted_at',
            'is_delete'=>'is_delete',
            'is_manual'=>'是否是后台手动充值'
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
