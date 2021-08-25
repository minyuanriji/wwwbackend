<?php

namespace app\plugins\area\models;

use app\models\BaseActiveRecord;

/**
 * This is the model class for table "{{%plugin_area_goods_detail}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property float $province_price
 * @property float $district_price
 * @property float $town_price
 * @property float $city_price
 * @property int $is_delete
 * @property int $updated_at
 * @property int $deleted_at
 * @property int $created_at
 * @property int|null $goods_id
 * @property int $area_goods_id area_goods表中的ID
 */
class AreaGoodsDetail extends BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%plugin_area_goods_detail}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'province_price', 'district_price', 'town_price', 'city_price', 'area_goods_id'], 'required'],
            [['mall_id', 'is_delete', 'updated_at', 'deleted_at', 'created_at', 'goods_id', 'area_goods_id'], 'integer'],
            [['province_price', 'district_price', 'town_price', 'city_price'], 'number'],
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
            'province_price' => 'Province Price',
            'district_price' => 'District Price',
            'town_price' => 'Town Price',
            'city_price' => 'City Price',
            'is_delete' => 'Is Delete',
            'updated_at' => 'Updated At',
            'deleted_at' => 'Deleted At',
            'created_at' => 'Created At',
            'goods_id' => 'Goods ID',
            'area_goods_id' => 'area_goods表中的ID',
        ];
    }
}