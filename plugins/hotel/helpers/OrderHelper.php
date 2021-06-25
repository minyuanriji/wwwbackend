<?php
namespace app\plugins\hotel\helpers;


use app\core\ApiCode;
use app\plugins\hotel\libs\IPlateform;
use app\plugins\hotel\models\HotelOrder;
use app\plugins\hotel\models\HotelPlateforms;

class OrderHelper{

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

            $classObject->submitOrder($order);

        }catch (\Exception $e){
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg'  => $e->getMessage()
            ];
        }
    }

    /**
     * 判断订单是否可以支付
     * @param HotelOrder $order
     * @return boolean
     */
    public static function isPayable(HotelOrder $order){
        return $order->order_status == "unpaid" && $order->pay_status == "unpaid";
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