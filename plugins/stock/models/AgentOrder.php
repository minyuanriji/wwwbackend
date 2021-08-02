<?php

namespace app\plugins\stock\models;

use app\models\BaseActiveRecord;
use Yii;

/**
 *
 * 代理商自用下单
 * This is the model class for table "{{%plugin_stock_agent_order}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property int $user_id
 * @property int $stock_goods_id
 * @property int $num
 * @property int $created_at
 * @property int $deleted_at
 * @property int $updated_at
 * @property int $is_delete
 * @property string|null $address
 * @property string|null $express_no
 * @property string|null $express_name
 * @property int $goods_id 商品ID
 * @property int $status 0待发货1待收货2确认收货
 * @property string $mobile
 * @property string $name
 */
class AgentOrder extends BaseActiveRecord
{
    //待发货
    const STATUS_WAIT_SEND = 0;
    //待收货
    const STATUS_WAIT_RECEIPT = 1;
    //已完成
    const STATUS_COMPLETE = 2;

    public static function tableName()
    {
        return '{{%plugin_stock_agent_order}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'user_id', 'stock_goods_id', 'goods_id'], 'required'],
            [['mall_id', 'user_id', 'stock_goods_id', 'num', 'created_at', 'deleted_at', 'updated_at', 'is_delete', 'goods_id','status'], 'integer'],
            [['name','mobile'],'string', 'max' => 45],
            [['address', 'express_no', 'express_name'], 'string', 'max' => 128],
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
            'stock_goods_id' => 'Stock Goods ID',
            'num' => 'Num',
            'created_at' => 'Created At',
            'deleted_at' => 'Deleted At',
            'updated_at' => 'Updated At',
            'is_delete' => 'Is Delete',
            'address' => 'Address',
            'express_no' => 'Express No',
            'express_name' => 'Express Name',
            'goods_id' => '商品ID',
            'status'=>'状态',
            'name'=>'收货人',
            'mobile'=>'手机'
        ];
    }
}