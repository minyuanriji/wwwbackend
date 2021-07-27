<?php

namespace app\plugins\boss\models;

use app\models\BaseActiveRecord;
use app\models\User;

/**
 * @property int $id
 * @property int $each_id
 * @property int $user_id
 * @property int $money
 * @property int $award_set
 * @property int $created_at
 * @property int $send_date
 * @property int $status
 * @property int $payment_time
 */
class BossAwardSentLog extends BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%plugin_boss_award_sent_log}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['each_id','user_id','money','award_set','send_date'], 'required'],
            [['each_id','user_id','created_at','status','payment_time'], 'integer'],
            [['award_set','send_date'], 'string'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'each_id' => '每期奖池记录 ID',
            'user_id' => '用户 ID',
            'money' => '分到金额',
            'award_set' => '奖金池配置',
            'created_at' => 'Created At',
            'send_date' => '发放日期',
            'status' => '状态',
            'payment_time' => '打款时间',
        ];
    }

    public function getUser ()
    {
        return $this->hasMany(User::className(), ['id' => 'user_id']);
    }

    public function getBossAwardEachLog ()
    {
        return $this->hasMany(BossAwardEachLog::className(), ['id' => 'each_id']);
    }
}
