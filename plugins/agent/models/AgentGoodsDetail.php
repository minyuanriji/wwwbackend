<?php

namespace app\plugins\agent\models;

use app\models\BaseActiveRecord;
use Yii;

/**
 * This is the model class for table "{{%plugin_agent_goods_detail}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property int $level
 * @property float $agent_price
 * @property float $equal_price
 * @property float $over_agent_price
 * @property int $is_delete
 * @property int $updated_at
 * @property int $deleted_at
 * @property int $created_at
 * @property int $goods_id
 * @property int $agent_goods_id
 */
class AgentGoodsDetail extends BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%plugin_agent_goods_detail}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'level', 'agent_price', 'equal_price', 'over_agent_price','goods_id'], 'required'],
            [['mall_id', 'level', 'is_delete', 'updated_at', 'deleted_at', 'created_at','goods_id','agent_goods_id'], 'integer'],
            [['agent_price', 'equal_price', 'over_agent_price'], 'number'],
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
            'agent_price' => 'Agent Price',
            'equal_price' => 'Equal Price',
            'over_agent_price' => 'Over Agent Price',
            'is_delete' => 'Is Delete',
            'updated_at' => 'Updated At',
            'deleted_at' => 'Deleted At',
            'created_at' => 'Created At',

            'goods_id'=>'商品ID',
            'agent_goods_id'=>'agent_goods_id'
        ];
    }
}