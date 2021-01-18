<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%score_log}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property int $user_id
 * @property int $type 类型：1=收入，2=支出
 * @property int $score 变动积分
 * @property int $current_score 当前积分
 * @property string $desc 变动说明
 * @property string $custom_desc 自定义详细说明|记录
 * @property int $created_at
 */
class ScoreLog extends BaseActiveRecord
{
    const TYPE_ADD = 1;
    const TYPE_SUB = 2;

    const EVENT_SCORE_CHANGE = "score_change"; //积分变动事件;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%score_log}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'user_id', 'type', 'score','current_score', 'custom_desc', 'created_at'], 'required'],
            [['mall_id', 'user_id', 'type'], 'integer'],
            [['score','current_score'], 'number'],
            [['custom_desc'], 'string'],
            [['created_at'], 'safe'],
            [['desc'], 'string', 'max' => 255],
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
            'score' => '变动积分',
            'current_score' => '当前积分',
            'desc' => '变动说明',
            'custom_desc' => '自定义详细说明|记录',
            'created_at' => 'Created At',
        ];
    }

    public function getUser()
    {
        return $this->hasOne(User::ClassName(), ['id' => 'user_id']);
    }
}
