<?php
namespace app\models;


class OrderClerkExpress extends BaseActiveRecord{

    /**
     * {@inheritdoc}
     */
    public static function tableName(){
        return '{{%order_clerk_express}}';
    }

    public function rules(){
        return [
            [['mall_id', 'order_id', 'order_detail_id', 'goods_id', 'express_detail_id', 'created_at', 'updated_at'], 'required'],
            [['store_id'], 'integer']
        ];
    }
}