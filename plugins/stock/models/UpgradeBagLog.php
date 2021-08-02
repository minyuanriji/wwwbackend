<?php

namespace app\plugins\stock\models;

use app\models\BaseActiveRecord;
use app\models\User;
use Yii;


/**
 * This is the model class for table "{{%plugin_stock_upgrade_bag_log}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property int $user_id
 * @property int $bag_id
 * @property int $common_order_detail_id
 * @property int $created_at
 * @property int $deleted_at
 * @property int $updated_at
 * @property int $is_delete
 */
class UpgradeBagLog extends BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%plugin_stock_upgrade_bag_log}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'user_id', 'bag_id', 'common_order_detail_id'], 'required'],
            [['mall_id', 'user_id', 'bag_id', 'common_order_detail_id', 'created_at', 'deleted_at', 'updated_at', 'is_delete'], 'integer'],
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
            'bag_id' => 'Bag ID',
            'common_order_detail_id' => 'Common Order Detail ID',
            'created_at' => 'Created At',
            'deleted_at' => 'Deleted At',
            'updated_at' => 'Updated At',
            'is_delete' => 'Is Delete',
        ];
    }
}
