<?php

namespace app\plugins\boss\models;

use app\models\BaseActiveRecord;

/**
 * This is the model class for table "{{%plugin_boss_awards}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property int $award_id
 * @property float $money 金额
 * @property float $money_front 充值前金额
 * @property float $money_after 充值后金额
 * @property int $source_id 如果是admin，这里是管理员ID
 * @property int $source_type admin管理员充值
 * @property string $remark 备注
 * @property int $created_at
 */
class BossAwardRechargeLog extends BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%plugin_boss_award_recharge_log}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id','award_id','money','source_id'], 'required'],
            [['mall_id','award_id', 'source_id', 'created_at'], 'integer'],
            [['source_type','remark'], 'string'],
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
            'award_id' => '奖金池ID',
            'money' => '充值金额',
            'money_front' => '充值前金额',
            'money_after' => '充值后金额',
            'source_id' => '管理员ID',
            'source_type' => 'admin管理员充值',
            'remark' => '备注',
            'created_at' => 'Created At',
        ];
    }
}
