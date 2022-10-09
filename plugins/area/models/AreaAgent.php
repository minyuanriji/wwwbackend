<?php

namespace app\plugins\area\models;

use app\models\BaseActiveRecord;
use app\models\User;

/**
 * This is the model class for table "{{%plugin_area_agent}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property int $user_id
 * @property string|null $remarks 备注
 * @property int $is_delete
 * @property int $created_at 创建时间
 * @property int $deleted_at 删除时间
 * @property int $updated_at 修改时间
 * @property int $total_childs 所有下级数量
 * @property int $total_order 订单数量
 * @property int $upgrade_level_at 区域等级升级时间
 * @property float $total_price 累计佣金
 * @property float $frozen_price 冻结佣金
 * @property string|null $delete_reason 删除原因
 * @property int $upgrade_status 1条件升级  2 购买指定商品升级   3手动升级
 * @property int $level 0VIP会员 1、镇代 2、区代 3、市代 4、省代
 * @property int $province_id 省ID
 * @property int $city_id 市id
 * @property int $district_id 区id
 * @property int $town_id 镇id
 */
class AreaAgent extends BaseActiveRecord
{
    const UPGRADE_STATUS_CONDITION = 1;
    const UPGRADE_STATUS_GOODS = 2;
    const UPGRADE_STATUS_MANUAL = 3;

    const LEVEL_TOWN=1;
    const LEVEL_DISTRICT=2;
    const LEVEL_CITY=3;
    const LEVEL_PROVINCE=4;

    const LEVEL = [
        0 => 'VIP会员',
        1 => '镇代',
        2 => '区代',
        3 => '市代',
        4 => '省代'
    ];

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%plugin_area_agent}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'user_id'], 'required'],
            [['mall_id', 'user_id', 'is_delete', 'created_at', 'deleted_at', 'updated_at', 'total_childs', 'total_order', 'upgrade_level_at', 'upgrade_status', 'level', 'province_id', 'city_id', 'district_id', 'town_id'], 'integer'],
            [['remarks', 'delete_reason'], 'string'],
            [['total_price', 'frozen_price'], 'number'],
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
            'remarks' => '备注',
            'is_delete' => 'Is Delete',
            'created_at' => '创建时间',
            'deleted_at' => '删除时间',
            'updated_at' => '修改时间',
            'total_childs' => '所有下级数量',
            'total_order' => '订单数量',
            'upgrade_level_at' => '区域等级升级时间',
            'total_price' => '累计佣金',
            'frozen_price' => '冻结佣金',
            'delete_reason' => '删除原因',
            'upgrade_status' => '1条件升级  2 购买指定商品升级   3手动升级',
            'level' => '0VIP会员 1、镇代 2、区代 3、市代 4、省代',
            'province_id' => '省ID',
            'city_id' => '市?id',
            'district_id' => '区id',
            'town_id' => '镇id',
        ];
    }

    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }
}
