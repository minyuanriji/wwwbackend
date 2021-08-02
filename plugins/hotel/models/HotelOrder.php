<?php
namespace app\plugins\hotel\models;


use app\models\BaseActiveRecord;

class HotelOrder extends BaseActiveRecord
{
    const PAY_STATUS_PAID = 'paid';  //已支付
    const ORDER_STATUS_SUCCESS = 'success';  //预订成功
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%plugin_hotel_orders}}';
    }

    public function rules()
    {
        return [
            [['mall_id', 'hotel_id', 'user_id', 'product_code', 'unique_id', 'order_no', 'order_status',
              'order_price', 'booking_num', 'booking_start_date', 'booking_days', 'real_booking_days', 'booking_passengers',
              'booking_arrive_date', 'created_at', 'updated_at', 'pay_status'], 'required'],
            [['pay_at', 'pay_type', 'pay_price', 'integral_deduction_price', 'integral_fee_rate', 'origin_booking_data',
              'commission_status', 'commission_remark',
              'commission_3r_status', 'commission_3r_remark'], 'safe']
        ];
    }

    /**
     * 获取平台信息
     * @return HotelPlateforms|null
     */
    public function getPlateform(){
        $plateform = HotelPlateforms::findOne([
            "source_code" => $this->order_no,
            "type"        => "order"
        ]);
        return $plateform;
    }
}