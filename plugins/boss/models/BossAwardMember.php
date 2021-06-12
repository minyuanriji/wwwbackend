<?php

namespace app\plugins\boss\models;

use app\models\BaseActiveRecord;
use app\models\User;

/**
 *
 * @property int $id
 * @property int $mall_id
 * @property int $award_id
 * @property int $user_id
 * @property int $created_at
 * @property int $updated_at
 */
class BossAwardMember extends BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%plugin_boss_award_member}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id','award_id','user_id'], 'required'],
            [['mall_id','award_id','user_id','created_at', 'updated_at'], 'integer'],
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
            'award_id' => '奖池 ID',
            'user_id' => '用户 ID',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    public function getUser()
    {
        return $this->hasMany(User::class, ['id' => 'user_id']);
    }
}
