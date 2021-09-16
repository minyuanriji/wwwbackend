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
              'created_at', 'updated_at', 'token'], 'required'],
            [['shopping_voucher_use_num', 'shopping_voucher_decode_price'], 'number'],
            [['remark', 'express', 'express_no'], 'string'],
            [['is_confirm', 'is_send', 'confirm_at', 'cancel_status', 'cancel_at', 'deleted_at', 'is_delete', 'is_recycle', 'is_comment',
              'comment_at', 'sale_status', 'status', 'auto_cancel_at', 'auto_confirm_at', 'auto_sales_at', 'complete_at',
              'send_at', 'address_id', 'pay_at'], 'integer']
        ];
    }












}