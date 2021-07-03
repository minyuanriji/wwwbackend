<?php

namespace app\plugins\boss\models;

use app\models\BaseActiveRecord;

/**
 *
 * @property int $id
 * @property int $awards_cycle
 * @property int $awards_id
 * @property int $money
 * @property int $people_num
 * @property int $money_front
 * @property int $money_after
 * @property int $rate
 * @property int $created_at
 * @property int $sent_time
 * @property int $actual_money
 */
class BossAwardEachLog extends BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%plugin_boss_award_each_log}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['awards_cycle','awards_id','money','people_num','money_front','money_after','rate'], 'required'],
            [['awards_id','people_num','created_at',], 'integer'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'awards_cycle' => '奖池期数',
            'award_id' => '奖池 ID',
            'money' => '发放金额',
            'people_num' => '发放人数',
            'money_front' => '发放前金额',
            'money_after' => '发放后金额',
            'rate' => '比例',
            'created_at' => 'Created At',
            'sent_time' => '发放时间',
        ];
    }

    public function getBossAwardSentLog ()
    {
        return $this->hasOne(BossAwardSentLog::className(), ['each_id' => 'id']);
    }
}
