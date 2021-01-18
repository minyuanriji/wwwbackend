<?php

namespace app\plugins\stock\models;

use app\models\BaseActiveRecord;
use Yii;

/**
 *
 * 购物者商城购物，代理商不够货，要去补货的队列记录
 *
 * This is the model class for table "{{%plugin_stock_fill_job}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property int $queue_id 队列ID
 * @property int $user_id
 * @property int $is_delete
 * @property int $created_at
 * @property int $deleted_at
 * @property int $updated_at
 * @property int $remain_num 最低需要补充这么多
 * @property int $goods_id
 * @property int $buy_user_id
 * @property int $common_order_detail_id
 * @property float $unit_price
 * @property int $fill_end_time
 */
class StockFillJob extends BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%plugin_stock_fill_job}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'user_id', 'goods_id', 'buy_user_id', 'common_order_detail_id'], 'required'],
            [['mall_id', 'queue_id', 'user_id', 'is_delete', 'created_at', 'deleted_at', 'updated_at', 'remain_num', 'goods_id', 'buy_user_id', 'common_order_detail_id','fill_end_time'], 'integer'],
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
            'queue_id' => '队列ID',
            'user_id' => 'User ID',
            'is_delete' => 'Is Delete',
            'created_at' => 'Created At',
            'deleted_at' => 'Deleted At',
            'updated_at' => 'Updated At',
            'remain_num' => '最低需要补充这么多',
            'goods_id' => 'Goods ID',
            'buy_user_id' => 'Buy User ID',
            'common_order_detail_id' => 'Common Order Detail ID',
            'unit_price' => 'Unit Price',
            'fill_end_time'=>'补货截止时间'
        ];
    }
}