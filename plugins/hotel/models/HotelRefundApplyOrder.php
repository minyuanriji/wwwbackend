<?php
namespace app\plugins\hotel\models;


use app\models\BaseActiveRecord;

class HotelRefundApplyOrder extends BaseActiveRecord
{

    const STATUS_REFUSED    = 'refused';  //拒绝
    const STATUS_PAID       = 'paid';     //已退款
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%plugin_hotel_refund_apply_order}}';
    }

    public function rules()
    {
        return [
            [['mall_id', 'refund_price', 'refund_integral', 'refund_balance', 'order_id', 'status', 'created_at', 'updated_at'], 'required'],
            [['remark'], 'safe']
        ];
    }

}