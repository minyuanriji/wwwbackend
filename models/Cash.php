<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%cash}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property int $user_id
 * @property string $order_no 订单号
 * @property float $price 提现金额
 * @property float $service_fee_rate 提现手续费（%）
 * @property string $type 提现方式 auto--自动打款 wechat--微信打款 alipay--支付宝打款 bank--银行转账 balance--打款到余额
 * @property string|null $extra 额外信息 例如微信账号、支付宝账号等
 * @property int $status 提现状态 0--申请 1--同意 2--已打款 3--驳回
 * @property int $is_delete
 * @property int $created_at 创建时间
 * @property int $deleted_at 删除时间
 * @property int $updated_at 修改时间
 * @property string|null $content
 * @property float $fact_price 实际到账
 * @property User $user
 */
class Cash extends BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%cash}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'user_id'], 'required'],
            [['mall_id', 'user_id', 'status', 'is_delete', 'created_at', 'deleted_at', 'updated_at', 'is_transmitting'], 'integer'],
            [['price', 'service_fee_rate', 'fact_price'], 'number'],
            [['extra', 'content'], 'string'],
            [['type'], 'string', 'max' => 45],
            [['order_no'], 'string', 'max' => 255],
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
            'order_no' => '订单号',
            'price' => '提现金额',
            'service_fee_rate' => '提现手续费（%）',
            'type' => '提现方式 auto--自动打款 wechat--微信打款 alipay--支付宝打款 bank--银行转账 balance--打款到余额',
            'extra' => '额外信息 例如微信账号、支付宝账号等',
            'status' => '提现状态 0--申请 1--同意 2--已打款 3--驳回',
            'is_delete' => 'Is Delete',
            'created_at' => '创建时间',
            'deleted_at' => '删除时间',
            'updated_at' => '修改时间',
            'content' => 'Content',
            'fact_price' => '实际到账'
        ];
    }

    public function getUser(){
           return $this->hasOne(User::class,['id'=>'user_id']);
    }
}