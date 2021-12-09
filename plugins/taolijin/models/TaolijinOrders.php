<?php

namespace app\plugins\taolijin\models;

use app\models\BaseActiveRecord;

class TaolijinOrders extends BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%plugin_taolijin_orders}}';
    }

    public function rules()
    {
        return [
            [['mall_id', 'ali_id', 'ali_order_sn', 'user_id', 'order_status',  'pay_price', 'pay_at', 'pay_status',
             'updated_at', 'created_at', 'ali_commission_price', 'ali_created_at', 'ali_commission_rate'], 'required'],
            [['ali_data', 'is_delete', 'ali_data'], 'safe']
        ];
    }

    /**
     * 计算订单状态
     * @param $order_status
     * @param $pay_status
     * @return string[]
     */
    public static function getStatusInfo($order_status, $pay_status){
        $infoArr = [
            'refund'    => ['status' => 'refund',    'text' => '已退款'],
            'refunding' => ['status' => 'refunding', 'text' => '退款中'],
            'finished'  => ['status' => 'finished',  'text' => '已结束'],
            'paid'      => ['status' => 'paid',      'text' => '已支付'],
            'unpaid'    => ['status' => 'unpaid',    'text' => '未支付'],
            'cancel'    => ['status' => 'cancel',    'text' => '已取消']
        ];
        $info = null;
        if($pay_status == "paid"){
            if($order_status == "finished"){
                $info = $infoArr['finished'];
            }else{
                $info = $infoArr['paid'];
            }
        }elseif($pay_status == "refunding" || $pay_status == "refund"){ //退款中、已退款
            $info = $infoArr[$pay_status];
        }else{ //未支付状态
            if($order_status == "cancel"){
                $info = $infoArr['cancel'];
            }else{
                $info = $infoArr['unpaid'];
            }
        }
        return $info;
    }
}
