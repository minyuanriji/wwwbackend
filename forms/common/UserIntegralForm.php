<?php
namespace app\forms\common;


use app\core\ApiCode;
use app\models\BaseModel;
use app\models\IntegralLog;
use app\models\IntegralRecord;
use app\models\User;
use app\plugins\hotel\models\HotelOrder;
use app\plugins\hotel\models\HotelRefundApplyOrder;

class UserIntegralForm extends BaseModel{

    const TYPE_ADD      = 1;
    const TYPE_SUB      = 2;

    /**
     * 酒店预订订单退款
     * @param HotelOrder $order
     * @param User $user
     * @param $price
     * @return array
     */
    public static function hotelOrderRefundAdd(HotelRefundApplyOrder $refundApplyOrder, User $user){
        $t = \Yii::$app->db->beginTransaction();
        try {
            $desc = "酒店预订单[".$refundApplyOrder->order_id."]取消，返还红包";

            static::change($user, $refundApplyOrder->refund_price, self::TYPE_ADD, "hotel_order_refund", $refundApplyOrder->id, $desc);

            $t->commit();

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg'  => '扣取成功'
            ];
        }catch (\Exception $e){
            $t->rollBack();
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg'  => $e->getMessage()
            ];
        }
    }

    /**
     * 支付酒店预订订单
     * @param HotelOrder $order
     * @param User $user
     * @param $price
     * @return array
     */
    public static function hotelOrderPaySub(HotelOrder $order, User $user, $price){
        $t = \Yii::$app->db->beginTransaction();
        try {
            $desc = "支付酒店预订订单“".$order->order_no."”";

            static::change($user, $price, self::TYPE_SUB, "hotel_order", $order->id, $desc);

            $t->commit();

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg'  => '扣取成功'
            ];
        }catch (\Exception $e){
            $t->rollBack();
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg'  => $e->getMessage()
            ];
        }
    }

    /**
     * 记录
     * @param User $user
     * @param IntegralRecord $record
     */
    public static function record(User $user, IntegralRecord $record){
        $t = \Yii::$app->db->beginTransaction();
        try {
            if($record->controller_type != 1){
                throw new \Exception("非红包记录类型无法操作");
            }

            $type  = $record->money >= 0 ? self::TYPE_ADD : self::TYPE_SUB;
            $desc  = $record->desc;
            $price = abs($record->money);

            static::change($user, $price, $type, "record", $record->id, $desc);

            $t->commit();

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg'  => '操作成功'
            ];
        }catch (\Exception $e){
            $t->rollBack();
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg'  => $e->getMessage()
            ];
        }

    }

    /**
     * 订单退款抵扣券返还
     * @param User $user
     * @param $price
     * @param $source_id
     * @return array
     */
    public static function orderRefundAdd(User $user, $price, $source_id){
        $t = \Yii::$app->db->beginTransaction();
        try {
            $desc = "订单[Detail Id:".$source_id."]退款退还红包券";

            static::change($user, $price, self::TYPE_ADD, "order_refund", $source_id, $desc);

            $t->commit();

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg'  => '操作成功'
            ];

        }catch (\Exception $e){
            $t->rollBack();
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg'  => $e->getMessage()
            ];
        }
    }

    /**
     * 结帐单红包抵扣
     * @param User $user
     * @param $price
     * @param $source_id
     * @return array
     */
    public static function checkoutSub(User $user, $price, $source_id, $desc = ""){
        $t = \Yii::$app->db->beginTransaction();
        try {
            if(empty($desc)){
                $desc = "红包券抵扣";
            }

            static::change($user, $price, self::TYPE_SUB, "checkout", $source_id, $desc);

            $t->commit();

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg'  => '操作成功'
            ];
        }catch (\Exception $e){
            $t->rollBack();
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg'  => $e->getMessage()
            ];
        }
    }

    /**
     * 管理员充值
     * @param User $user
     * @param $price
     * @param string $remark
     * @return void
     */
    public static function adminAdd(User $user, $price, $admin_id, $remark = "", $is_manual = 0){
        $t = \Yii::$app->db->beginTransaction();
        try {

            $desc = "管理员[ID:{$admin_id}]充值：" . $remark;

            static::change($user, $price, self::TYPE_ADD, "admin", null, $desc, $is_manual);

            $t->commit();

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg'  => '操作成功'
            ];
        }catch (\Exception $e){
            $t->rollBack();
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg'  => $e->getMessage()
            ];
        }
    }

    /**
     * 管理员扣减
     * @param User $user
     * @param $price
     * @param string $remark
     * @return void
     */
    public static function adminSub(User $user, $price, $admin_id, $remark = "", $is_manual = 0){
        $t = \Yii::$app->db->beginTransaction();
        try {

            $desc = "管理员[ID:{$admin_id}]扣减：" . $remark;

            static::change($user, $price, self::TYPE_SUB, "admin", null, $desc, $is_manual);

            $t->commit();

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg'  => '操作成功'
            ];
        }catch (\Exception $e){
            $t->rollBack();
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg'  => $e->getMessage()
            ];
        }
    }

    private static function change(User $user, $price, $type, $source_type, $source_id, $desc = null, $is_manual = 0){

        $staticIntegral = floatval($user->static_integral);

        if($type == 1){ //收入
            $user->static_integral = $staticIntegral + $price;
        }else{ //支出
            $user->static_integral = max(0, $staticIntegral - floatval($price));
        }

        if(!$user->save()){
            throw new \Exception(json_encode($user->getErrors()));
        }

        if($desc === null){
            $desc = "";
        }

        $integralLog = new IntegralLog([
            "mall_id"          => $user->mall_id,
            "user_id"          => $user->id,
            "type"             => $type,
            "current_integral" => $staticIntegral,
            "integral"         => floatval($price),
            "desc"             => $desc,
            "source_id"        => $source_id,
            "source_type"      => $source_type,
            "created_at"       => time(),
            "is_manual"       => $is_manual
        ]);
        if(!$integralLog->save()){
            throw new \Exception(json_encode($integralLog->getErrors()));
        }
    }
}