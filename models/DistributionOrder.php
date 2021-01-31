<?php
namespace app\models;


class DistributionOrder extends BaseActiveRecord{
    /**
     * {@inheritdoc}
     */
    public static function tableName(){
        return '{{%plugin_distribution_order}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules(){
        return [
            [['mall_id', 'order_id', 'user_id'], 'required'],
            [['mall_id', 'order_id', 'order_detail_id', 'user_id',
              'first_parent_id', 'second_parent_id', 'third_parent_id',
              'created_at', 'deleted_at', 'updated_at', 'is_refund',
                'is_pay', 'is_transfer', 'is_delete'], 'integer'],
            [['first_price', 'second_price', 'third_price'], 'number']
        ];
    }

}
