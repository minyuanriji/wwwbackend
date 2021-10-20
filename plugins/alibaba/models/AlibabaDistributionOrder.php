<?php

namespace app\plugins\alibaba\models;

use app\models\BaseActiveRecord;

class AlibabaDistributionOrder extends BaseActiveRecord{

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%plugin_alibaba_distribution_order}}';
    }

    public function rules(){
        return [
            [['mall_id', 'user_id', 'order_no', 'total_price', 'total_pay_price', 'express_original_price', 'express_price',
              'total_goods_price', 'total_goods_original_price', 'name', 'mobile', 'address', 'is_pay', 'pay_type',
              'created_at', 'updated_at', 'token', 'ali_address_info'], 'required'],
            [['shopping_voucher_use_num', 'shopping_voucher_decode_price', 'shopping_voucher_express_use_num', 'shopping_voucher_express_decode_price'], 'number'],
            [['remark', 'close_reason'], 'string'],
            [['is_closed', 'deleted_at', 'is_delete', 'is_recycle',  'complete_at', 'address_id', 'pay_at'], 'integer']
        ];
    }

    /**
     * 获取订单详情记录
     * @return \yii\db\ActiveQuery
     */
    public function getOrderDetails(){
       return $this->hasMany(AlibabaDistributionOrderDetail::class, ["order_id" => "id"]);
    }

}