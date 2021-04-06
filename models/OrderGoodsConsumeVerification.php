<?php
namespace app\models;


class OrderGoodsConsumeVerification extends BaseActiveRecord{

    const STATUS_USED   = 1;
    const STATUS_UNUSED = 0;

    public static function tableName(){
        return '{{%order_goods_consume_verification}}';
    }

    public function rules(){
        return [
            [['mall_id', 'mch_id', 'order_id', 'order_detail_id', 'goods_id', 'user_id', 'verification_code', 'created_at', 'updated_at'], 'required'],
            [['mall_id', 'mch_id', 'order_id', 'order_detail_id', 'goods_id', 'user_id', 'is_used'], 'integer'],
            [['verification_code'], 'string', 'max' => 64],
        ];
    }

    public function attributeLabels(){
        return [
            'id'                    => 'ID',
            'mall_id'               => '商城ID',
            'mch_id'                => '商户ID',
            'order_id'              => '订单ID',
            'order_detail_id'       => '订单详情ID',
            'goods_id'              => '商品ID',
            'user_id'               => '用户ID',
            'verification_code'     => '核销码',
            'is_used'               => '是否已使用',
            'is_delete'             => 'Is Delete',
            'created_at'            => 'Created At',
            'updated_at'            => 'Updated At'
        ];
    }
    public function getOrder() {
        return $this->hasOne(Order::className(), ['id' => 'order_id']);
    }

    public function getGoods() {
        return $this->hasOne(Goods::className(), ['id' => 'goods_id']);
    }

    public function getOrderDetail() {
        return $this->hasOne(OrderDetail::className(), ['id' => 'order_detail_id']);
    }
}