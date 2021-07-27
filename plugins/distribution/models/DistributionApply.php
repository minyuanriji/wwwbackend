<?php

namespace app\plugins\distribution\models;

use app\models\BaseActiveRecord;
use app\models\User;
use app\plugins\area\events\AreaInsertEvent;
use app\plugins\area\handlers\AreaInsertHandler;
use Yii;

/**
 * This is the model class for table "{{%plugin_area_apply}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property int $user_id
 * @property int $status
 * @property int $created_at
 * @property int $updated_at
 * @property int $deleted_at
 * @property int $is_delete
 * @property string|null $address
 * @property string $realname
 * @property string $mobile
 * @property string|null $marks
 *
 */
class DistributionApply extends BaseActiveRecord
{
    //待审核
    const STATUS_WAIT_REVIEW = 0;
    //审核通过
    const STATUS_PASS = 1;
    //审核不通过
    const STATUS_NO_PASS = 2;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%plugin_distribution_apply}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'user_id', 'realname', 'mobile'], 'required'],
            [['mall_id', 'user_id', 'status', 'created_at', 'updated_at', 'deleted_at', 'is_delete'], 'integer'],
            [['realname', 'mobile'], 'string', 'max' => 45],
            [['marks'], 'string', 'max' => 255],
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
            'status' => 'Status',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'deleted_at' => 'Deleted At',
            'is_delete' => 'Is Delete',
            'realname' => 'Realname',
            'mobile' => 'Mobile',
            'marks' => 'Marks',
        ];

    }

    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }


}
