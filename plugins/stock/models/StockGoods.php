<?php

namespace app\plugins\stock\models;

use app\models\BaseActiveRecord;
use app\models\Goods;
use Yii;

/**
 * This is the model class for table "{{%plugin_stock_goods}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property int $goods_id
 * @property float $origin_price
 * @property string|null $agent_price 代理商商品
 * @property int $created_at
 * @property int $deleted_at
 * @property int $updated_at
 * @property int $is_delete
 * @property int $goods_type 0 商城商品
 * @property Goods $goods
 * @property string $equal_level_list
 * @property string $fill_level_list
 * @property string $over_level_list
 */
class StockGoods extends BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%plugin_stock_goods}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'goods_id', 'created_at', 'deleted_at', 'updated_at'], 'required'],
            [['mall_id', 'goods_id', 'created_at', 'deleted_at', 'updated_at', 'is_delete', 'goods_type'], 'integer'],
            [['origin_price'], 'number'],
            [['agent_price','equal_level_list','fill_level_list','over_level_list'], 'string', 'max' => 512],
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
            'goods_id' => 'Goods ID',
            'origin_price' => 'Origin Price',
            'agent_price' => '代理商商品',
            'created_at' => 'Created At',
            'deleted_at' => 'Deleted At',
            'updated_at' => 'Updated At',
            'is_delete' => 'Is Delete',
            'goods_type' => '0 商城商品',
            'equal_level_list'=>'平级奖配置',
            'fill_level_list'=>'补货奖励',
            'over_level_list'=>'越级奖'
        ];
    }

    public function getGoods()
    {
        return $this->hasOne(Goods::class, ['id' => 'goods_id']);
    }
}
