<?php
namespace app\plugins\hotel\models;


use app\models\BaseActiveRecord;

class HotelOrder extends BaseActiveRecord
{
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
              'order_price', 'booking_num', 'booking_start_date', 'booking_days', 'booking_passengers',
              'booking_arrive_date', 'created_at', 'updated_at', 'pay_status', 'origin_booking_data'], 'required'],
            [['pay_at', 'pay_price', 'integral_deduction_price', 'integral_fee_rate'], 'safe']
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