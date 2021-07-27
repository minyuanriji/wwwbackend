<?php

namespace app\plugins\distribution\models;

use app\models\BaseActiveRecord;
use app\models\User;
use Yii;

/**
 * This is the model class for table "{{%plugin_distribution_subsidy_price_log}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property int $user_id
 * @property float $price
 * @property string $month
 * @property int $team_new_count
 * @property int $deleted_at
 * @property int $created_at
 * @property int $updated_at
 * @property int $is_delete
 */
class SubsidyPriceLog extends BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%plugin_distribution_subsidy_price_log}}';
    }


    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'user_id', 'month'], 'required'],
            [['mall_id', 'user_id', 'team_new_count', 'deleted_at', 'created_at', 'updated_at', 'is_delete'], 'integer'],
            [['price'], 'number'],
            [['month'], 'string', 'max' => 45],
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
            'price' => 'Price',
            'month' => 'Month',
            'team_new_count' => 'Team New Count',
            'deleted_at' => 'Deleted At',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'is_delete' => 'Is Delete',
        ];
    }


    public function getUser()
    {

        return $this->hasOne(User::class, ['id' => 'user_id']);

    }
}
