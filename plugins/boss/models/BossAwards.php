<?php

namespace app\plugins\boss\models;

use app\models\BaseActiveRecord;

/**
 * This is the model class for table "{{%plugin_boss_awards}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property string $award_sn 编号
 * @property string $name 名称
 * @property int $status 状态：0未开时，1奖金发放中，2已结束
 * @property int $period 结算周期时间
 * @property int $period_unit 结算周期类型
 * @property int $rate 当前奖金池比例
 * @property float $money 奖金池总金额
 * @property int $created_at
 * @property int $updated_at
 * @property int $is_delete
 * @property int $deleted_at
 * @property int $last_send_time
 * @property int $next_send_time
 * @property int $automatic_audit
 * @property int $level_id
 */
class BossAwards extends BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%plugin_boss_awards}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id'], 'required'],
            [['mall_id','created_at', 'updated_at', 'is_delete','status','deleted_at','period','automatic_audit'], 'integer'],
            [['award_sn','period_unit','level_id'], 'string'],
            [['money'], 'number'],
            [['name'], 'string', 'max' => 45],
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
            'award_sn' => '编号',
            'name' => 'Name',
            'status' => '状态',
            'money' => '奖金池总金额',
            'period' => '结算周期时间',
            'period_unit' => '结算周期类型',
            'rate' => '当前奖金池比例',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'is_delete' => 'Is Delete',
            'deleted_at' => '删除时间',
            'automatic_audit' => '自动审核',
            'level_id' => '等级ID',
        ];
    }
}
