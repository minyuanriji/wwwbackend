<?php

namespace app\plugins\stock\models;

use app\models\BaseActiveRecord;
use app\models\Goods;
use Yii;

/**
 * This is the model class for table "{{%plugin_stock_agent_goods}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property int $user_id
 * @property int $goods_id
 * @property int $num
 * @property int $created_at
 * @property int $deleted_at
 * @property int $updated_at
 * @property int $is_delete
 * @property float $sale_price
 */
class StockAgentGoods extends BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%plugin_stock_agent_goods}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'user_id', 'goods_id'], 'required'],
            [['mall_id', 'user_id', 'goods_id', 'num', 'created_at', 'deleted_at', 'updated_at', 'is_delete'], 'integer'],
            [['sale_price'],'number']
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
            'goods_id' => 'Goods ID',
            'num' => 'Num',
            'created_at' => 'Created At',
            'deleted_at' => 'Deleted At',
            'updated_at' => 'Updated At',
            'is_delete' => 'Is Delete',
            'sale_price'=>'售价'
        ];
    }
}
