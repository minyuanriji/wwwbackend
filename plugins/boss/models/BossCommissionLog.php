<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 佣金明细日志model
 * Author: zal
 * Date: 2020-05-25
 * Time: 15:35
 */

namespace app\plugins\boss\models;

use app\models\BaseActiveRecord;
use app\models\Order;
use app\models\User;
use Yii;

/**
 * This is the model class for table "{{%commission_log}}".
 *
 * @property int $id
 * @property int $user_id
 * @property int $mall_id
 * @property int $order_id
 * @property int $boss_order_id 分销订单id
 * @property int $type 类型：0=未知,1=收入，2=支出
 * @property int $level 佣金来源1直推2间推3团队分红
 * @property int $status 佣金状态0冻结1已结算
 * @property int $is_pay 是否已支付0未支付1已支付
 * @property double $money 变动金额
 * @property double $commission 当前佣金
 * @property string $desc 变动说明
 * @property int $created_at
 *
 * @property Order $order
 * @property BossOrder $bossOrder
 */
class BossCommissionLog extends BaseActiveRecord
{
    /** @var int 类型 0未知，1收入，2支出 */
    const TYPE_NO = 0;
    const TYPE_IN = 1;
    const TYPE_OUT = 2;

    /** @var int 佣金来源 1一级分佣，2二级分佣，3三级分佣 */
    const LEVEL_FIRST = 1;
    const LEVEL_SECOND = 2;
    const LEVEL_THIRD = 3;

    /** @var int 佣金状态0冻结1已结算 */
    const STATUS_FROZEN = 0;
    const STATUS_SETTLE = 1;

    /** @var int 佣金状态0未支付1已支付 */
    const IS_PAY_YES = 1;
    const IS_PAY_NO = 0;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%plugin_boss_commission_log}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id', 'mall_id', 'type', 'money','commission', 'desc','boss_order_id', 'created_at'], 'required'],
            [['user_id', 'mall_id', 'type','level','boss_order_id','status','order_id','is_pay'], 'integer'],
            [['money','commission'], 'number'],
            [['desc'], 'string'],
            [['created_at'], 'safe'],
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
            'order_id' => 'Order ID',
            'boss_order_id' => '分销订单id',
            'type' => '类型：1=收入，2=支出',
            'is_pay' => '是否支付0未1是',
            'level' => '佣金来源1直推2间推3团队分红',
            'status' => '佣金状态0冻结1已结算',
            'money' => '变动金额',
            'commission' => '当前佣金',
            'desc' => '变动说明',
            'created_at' => 'Created At',
        ];
    }

    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }

    public function getOrder()
    {
        return $this->hasOne(Order::className(), ['id' => 'order_id']);
    }

    public function getBossOrder()
    {
        return $this->hasOne(BossOrder::className(), ['id' => 'boss_order_id']);
    }
}
