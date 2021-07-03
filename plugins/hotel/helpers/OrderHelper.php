<?php
namespace app\plugins\hotel\helpers;


use app\core\ApiCode;
use app\plugins\hotel\libs\IPlateform;
use app\plugins\hotel\libs\plateform\OrderRefundResult;
use app\plugins\hotel\libs\plateform\QueryOrderResult;
use app\plugins\hotel\libs\plateform\SubmitOrderResult;
use app\plugins\hotel\models\HotelOrder;
use app\plugins\hotel\models\HotelPlateforms;

class OrderHelper{

    public static function orderRefund(){

    }

    /**
     * 第三方平台订单退款
     * @param HotelOrder $order
     * @param HotelPlateforms $plateform
     */
    public static function plateformOrderRefundApply(HotelOrder $order, HotelPlateforms $plateform){
        try {
            $className = $plateform->plateform_class;
            if(empty($className) || !class_exists($className)){
                throw new \Exception("缺失平台类文件");
            }
            $classObject = new $className();
            if(!$classObject instanceof IPlateform){
                throw new \Exception("平台类文件未实现IPlateform接口");
            }

            $result = $classObject->orderRefund($order);
            if(!$result instanceof OrderRefundResult){
                throw new \Exception("结果对象返回类型[OrderRefundResult]错误");
            }

            if($result->code != OrderRefundResult::CODE_SUCC){
                throw new \Exception($result->message);
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'data' => []
            ];
        }catch (\Exception $e){
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg'  => $e->getMessage()
            ];
        }
    }

    /**
     * 判断订单状态
     * @param string $order_status
     * @param string $pay_status
     * @param integer $created_at
     * @param string $start_date
     * @param integer $days
     * @return string[]
     */
    public static function getOrderRealStatus($order_status, $pay_status, $created_at, $start_date, $days){
        $endTime = strtotime($start_date) + $days * 3600 * 24;
        $texts = ["finished" => "已结束", "fail" => "预订失败", "refund" => "已退款", "refunding" => "退款中", "confirmed" => "已确认", "cancel" => "已取消", "expired" => "已失效", "unpaid" => "未支付", "unconfirmed" => "待确认"];
        $info = ['text' => '', 'status' => 'none'];
        if($pay_status == "paid"){ //已支付
            if($order_status == "unconfirmed"){ //未确认
                $info['status'] = $order_status;
            }elseif($order_status == "success"){  //预订成功
                if($endTime < time()){ //已结束
                    $info['status'] = "finished";
                }else{ //已确认
                    $info['status'] = "confirmed";
                }
            }elseif($order_status == "finished"){ //已结束
                $info['status'] = $order_status;
            }elseif($order_status == "fail"){ //预订失败
                $info['status'] = $order_status;
            }
        }elseif($pay_status == "refunding"){ //退款中
            $info['status'] = $pay_status;
        }elseif($pay_status == "refund"){ //已退款
            $info['status'] = $pay_status;
        }else{
            if($order_status == "cancel"){ //已取消
                $info['status'] = $order_status;
            }elseif((time() - $created_at) > 60 * 15){
                //如果下单日期超过15分钟未支付，设为失效订单
                $info['status'] = "expired";
            }else{ //未支付
                $info['status'] = "unpaid";
            }
        }

        $info['text'] = isset($texts[$info['status']]) ? $texts[$info['status']] : "";

        return $info;
    }

    /**
     * 第三方平台订单查询
     * @param HotelOrder $order
     * @param HotelPlateforms $plateform
     */
    public static function queryPlateformOrder(HotelOrder $order, HotelPlateforms $plateform){
        try {
            $className = $plateform->plateform_class;
            if(empty($className) || !class_exists($className)){
                throw new \Exception("缺失平台类文件");
            }
            $classObject = new $className();
            if(!$classObject instanceof IPlateform){
                throw new \Exception("平台类文件未实现IPlateform接口");
            }

            $result = $classObject->queryOrder($order);
            if(!$result instanceof QueryOrderResult){
                throw new \Exception("结果对象返回类型[QueryOrderResult]错误");
            }

            if($result->code != QueryOrderResult::CODE_SUCC){
                throw new \Exception($result->message);
            }

            $orderState = 0;
            if(in_array($result->order_state, [0, 1, 2, 3, 4, 5, 6])){
                $orderState = (int)$result->order_state;
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'data' => [
                    "plateform_order_no" => $result->plateform_order_no,
                    "order_state"        => $result->order_state,
                    "pay_state"          => $result->pay_state,
                    "pay_type"           => $result->pay_type
                ]
            ];
        }catch (\Exception $e){
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg'  => $e->getMessage()
            ];
        }
    }

    /**
     * 第三方平台下单
     * @param HotelOrder $order
     * @param HotelPlateforms $plateform
     */
    public static function submitPlateformOrder(HotelOrder $order, HotelPlateforms $plateform){
        try {
            $className = $plateform->plateform_class;
            if(empty($className) || !class_exists($className)){
                throw new \Exception("缺失平台类文件");
            }
            $classObject = new $className();
            if(!$classObject instanceof IPlateform){
                throw new \Exception("平台类文件未实现IPlateform接口");
            }

            $result = $classObject->submitOrder($order);
            if(!$result instanceof SubmitOrderResult){
                throw new \Exception("结果对象返回类型[SubmitOrderResult]错误");
            }

            if($result->code != SubmitOrderResult::CODE_SUCC){
                throw new \Exception($result->message);
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'data' => [
                    'plateform_order_no' => $result->plateform_order_no,
                    'is_success'         => $result->is_success ? 1 : 0
                ]
            ];
        }catch (\Exception $e){
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg'  => $e->getMessage()
            ];
        }
    }

    /**
     * 方法1：判断订单是否可以支付
     * @param HotelOrder $order
     * @return boolean
     */
    public static function isPayable(HotelOrder $order){
        return static::isPayable2($order->order_status, $order->pay_status, $order->created_at, $order->booking_start_date, $order->booking_days);
    }

    /**
     * 方法2：判断订单是否可以支付
     * @param string $order_status
     * @param string $pay_status
     * @param integer $created_at
     * @param string $start_date
     * @param integer $days
     * @return bool
     */
    public static function isPayable2($order_status, $pay_status, $created_at, $start_date, $days){
        $statusInfo = static::getOrderRealStatus($order_status, $pay_status, $created_at, $start_date, $days);
        return $statusInfo['status'] == "unpaid";
    }

    /**
     * 判断订单是否可以取消
     * @param $order_status
     * @param $pay_status
     * @param $created_at
     * @param $start_date
     * @param $days
     * @return bool
     */
    public static function isCancelable($order_status, $pay_status, $created_at, $start_date, $days){
        $statusInfo = static::getOrderRealStatus($order_status, $pay_status, $created_at, $start_date, $days);
        return in_array($statusInfo['status'], ["unpaid", "expired"]);
    }

    /**
     * 判断是否可以退款
     * @param HotelOrder $hotelOrder
     * @return boolean
     */
    public static function isRefundable(HotelOrder $hotelOrder){
        $statusInfo = static::getOrderRealStatus($hotelOrder->order_status, $hotelOrder->pay_status, $hotelOrder->created_at, $hotelOrder->booking_start_date, $hotelOrder->booking_days);
        $isRefundable = false;
        if(in_array($statusInfo['status'], ["confirmed", "unconfirmed"])){
            try {
                $plateform = HotelPlateforms::findOne([
                    "type" => "order",
                    "source_code" => $hotelOrder->order_no
                ]);
                if(!$plateform){
                    throw new \Exception("无法获取平台信息");
                }
                $className = $plateform->plateform_class;
                if(empty($className) || !class_exists($className)){
                    throw new \Exception("缺失平台类文件");
                }
                $classObject = new $className();
                if(!$classObject instanceof IPlateform){
                    throw new \Exception("平台类文件未实现IPlateform接口");
                }

                $isRefundable = $classObject->refundable($hotelOrder);

            }catch (\Exception $e){
                $isRefundable = false;
            }
        }
        return $isRefundable;
    }

    /**
     * 计算如果使用红包抵扣需要的数量
     * @param float $price
     * @return float
     */
    public static function getIntegralPrice($price){
        return $price;
    }
}