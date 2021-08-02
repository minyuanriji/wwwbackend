<?php

namespace app\plugins\stock\models;

use app\models\BaseActiveRecord;
use app\models\Goods;
use app\models\User;
use app\plugins\stock\events\StockInsertEvent;
use app\plugins\stock\handlers\StockInsertHandler;
use Yii;

/**
 * This is the model class for table "{{%plugin_stock_upgrade_bag}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property int $level
 * @property int $goods_id
 * @property int $stock_num
 * @property int $is_stock
 * @property float $unit_price
 * @property int $stock_goods_id
 * @property int $updated_at
 * @property int $deleted_at
 * @property int $created_at
 * @property int $is_delete
 * @property Goods $goods
 * @property string $name
 * @property string $is_enable
 * @property int $compute_type
 */
class UpgradeBag extends BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%plugin_stock_upgrade_bag}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'level', 'goods_id', 'name'], 'required'],
            [['mall_id', 'level', 'goods_id', 'compute_type', 'is_enable', 'stock_num', 'is_stock', 'stock_goods_id', 'updated_at', 'deleted_at', 'created_at', 'is_delete', 'is_enable'], 'integer'],
            [['unit_price'], 'number'],
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
            'goods_id' => 'Goods ID',
            'stock_num' => 'Stock Num',
            'is_stock' => 'Is Stock',
            'unit_price' => 'Unit Price',
            'stock_goods_id' => 'Stock Goods ID',
            'updated_at' => 'Updated At',
            'deleted_at' => 'Deleted At',
            'created_at' => 'Created At',
            'is_delete' => 'Is Delete',
            'name' => 'name',
            'is_enable' => '是否启用',
            'compute_type' => '0 完成 1 支付'
        ];
    }

    public function getGoods()
    {
        return $this->hasOne(Goods::class, ['id' => 'goods_id']);

    }
}
