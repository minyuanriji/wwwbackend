<?php

namespace app\plugins\distribution\models;

use app\models\BaseActiveRecord;

use Yii;

/**
 * This is the model class for table "{{%plugin_distribution_rebuy_level}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property int $level
 * @property int $price_type 0百分比  1固定金额
 * @property float $price 佣金类型
 * @property int $is_delete
 * @property int $deleted_at
 * @property int $created_at
 * @property int $updated_at
 * @property int $is_enable
 * @property int $distribution_level
 * @property string $name
 * @property int $upgrade_type
 * @property int $child_num
 * @property int $team_child_num
 */
class RebuyLevel extends BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%plugin_distribution_rebuy_level}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'level'], 'required'],
            [['name'], 'string'],
            [['mall_id', 'team_child_num', 'level', 'price_type', 'is_delete', 'deleted_at', 'created_at', 'updated_at', 'is_enable', 'distribution_level', 'upgrade_type', 'child_num'], 'integer'],
            [['team_child_num'], 'default', 'value' => -1],
            [['price'], 'number'],
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
            'level' => 'Level',
            'price_type' => '0百分比  1固定金额',
            'price' => '佣金类型',
            'is_delete' => 'Is Delete',
            'deleted_at' => 'Deleted At',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'is_enable' => 'Is Enable',
            'distribution_level' => '分销商等级',
            'name' => '等级名称',
            'child_num' => '邀请人数',
            'upgrade_type' => '升级类型',
            'team_child_num' => '团队总邀请人数'
        ];
    }
}
