<?php

namespace app\plugins\stock\models;

use app\models\BaseActiveRecord;
use Yii;

/**
 *
 * 代理商补货的时候，上级代理商不够货需要去补货的记录队列
 * This is the model class for table "{{%plugin_stock_agent_fill_job}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property int $user_id
 * @property int $goods_id
 * @property int $remain_num
 * @property int $fill_order_detail_id
 * @property float $unit_price
 * @property int $queue_id
 * @property int $updated_at
 * @property int $deleted_at
 * @property int $created_at
 * @property int $is_delete
 * @property int $fill_end_time
 */
class AgentFillJob extends BaseActiveRecord
{
    public static function tableName()
    {
        return '{{%plugin_stock_agent_fill_job}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'user_id', 'goods_id', 'remain_num', 'fill_order_detail_id', 'queue_id'], 'required'],
            [['mall_id', 'user_id', 'goods_id', 'remain_num', 'fill_order_detail_id', 'queue_id', 'updated_at', 'deleted_at', 'created_at', 'is_delete','fill_end_time'], 'integer'],
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
            'user_id' => 'User ID',
            'goods_id' => 'Goods ID',
            'remain_num' => 'Remain Num',
            'fill_order_detail_id' => 'Fill Order Detail ID',
            'unit_price' => 'Unit Price',
            'queue_id' => 'Queue ID',
            'updated_at' => 'Updated At',
            'deleted_at' => 'Deleted At',
            'created_at' => 'Created At',
            'is_delete' => 'Is Delete',
            'fill_end_time'=>'fill_end_time'
        ];
    }
}