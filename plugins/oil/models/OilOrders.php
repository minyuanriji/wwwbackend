<?php
namespace app\plugins\oil\models;


use app\models\BaseActiveRecord;

class OilOrders extends BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%plugin_oil_orders}}';
    }

    public function rules()
    {
        return [
            [['mall_id', 'user_id', 'mobile', 'product_id', 'order_no', 'order_status', 'order_price',
              'created_at', 'updated_at', 'pay_status', 'province_id', 'province', 'city_id', 'city',
              'location', 'poi_type'], 'required'],
            [['address', 'district_id', 'district', 'pay_at', 'pay_price', 'pay_type', 'integral_deduction_price',
              'integral_fee_rate', 'transfer_rate', 'transfer_amount'], 'safe']
        ];
    }

    /**
     * 获取订单状态
     * @param $order_status
     * @param $pay_status
     * @param $created_at
     * @return array
     */
    public static function getStatusInfo($order_status, $pay_status, $created_at){
        $allStatus = [
            "unconfirmed" => "待确认",
            "wait"        => "待使用",
            "fail"        => "确认失败",
            "finished"    => "已完成",
            "refund"      => "已退款",
            "refunding"   => "退款中",
            "expired"     => "已过期",
            "invalid"     => "无效订单",
            "unpaid"      => "未支付"
        ];
        $info = ["status" => "-1"];
        if($pay_status == "paid" && !in_array($order_status, ["cancel", "unpaid"])){ //已支付
            $info['status'] = $order_status;
        }elseif(in_array($pay_status, ["refund", "refunding"])) {  //退款中、已退款
            $info['status'] = $pay_status;
        }elseif($order_status == "unpaid" && (time() - 12 * 3600) > $created_at){
            $info['status'] = "expired";
        }else{ //无效订单
            $info['status'] = in_array($order_status, ["cancel", "unpaid"]) ? $order_status : "invalid";
        }
        $info['text'] = isset($allStatus[$info['status']]) ? $allStatus[$info['status']] : "";
        return $info;
    }
}