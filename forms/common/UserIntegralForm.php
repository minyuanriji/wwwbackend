<?php
namespace app\forms\common;

use app\core\ApiCode;
use app\models\BaseModel;
use app\models\IntegralLog;
use app\models\IntegralRecord;
use app\models\User;
use app\plugins\addcredit\models\AddcreditOrder;
use app\plugins\giftpacks\models\GiftpacksGroupPayOrder;
use app\plugins\giftpacks\models\GiftpacksOrder;
use app\plugins\hotel\models\HotelOrder;
use app\plugins\hotel\models\HotelRefundApplyOrder;

class UserIntegralForm extends BaseModel{

    const TYPE_ADD      = 1;
    const TYPE_SUB      = 2;

    /**
     * 支付大礼包拼团订单
     * @param HotelOrder $order
     * @param User $user
     * @param boolean $trans
     * @return array
     */
    public static function giftpacksGroupPaySub(GiftpacksGroupPayOrder $payOrder, User $user, $trans = false){

        $trans && ($t = \Yii::$app->db->beginTransaction());

        try {
            $desc = "大礼包拼单支付订单[ID:".$payOrder->id."]";
            static::change($user, $payOrder->integral_deduction_price, self::TYPE_SUB, "giftpacks_group_payorder", $payOrder->id, $desc);

            $trans && $t->commit();

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg'  => '扣取成功'
            ];
        }catch (\Exception $e){

            $trans && $t->rollBack();

            return [
                'code' => ApiCode::CODE_FAIL,
                'msg'  => $e->getMessage()
            ];
        }
    }


    /**
     * 支付大礼包订单订单
     * @param HotelOrder $order
     * @param User $user
     * @param boolean $trans
     * @return array
     */
    public static function giftpacksOrderPaySub(GiftpacksOrder $order, User $user, $trans = false){

        $trans && ($t = \Yii::$app->db->beginTransaction());

        try {
            $desc = "支付大礼包“".$order->order_sn."”订单";
            static::change($user, $order->integral_deduction_price, self::TYPE_SUB, "giftpacks_order", $order->id, $desc);

            $trans && $t->commit();

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg'  => '扣取成功'
            ];
        }catch (\Exception $e){

            $trans && $t->rollBack();

            return [
                'code' => ApiCode::CODE_FAIL,
                'msg'  => $e->getMessage()
            ];
        }


    }

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
            $desc = "支付酒店预订“".$order->order_no."”订单";

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
    public static function adminAdd(User $user, $price, $admin_id, $remark = ""){
        $t = \Yii::$app->db->beginTransaction();
        try {

            $desc = "管理员[ID:{$admin_id}]充值：" . $remark;

            static::change($user, $price, self::TYPE_ADD, "admin", null, $desc);

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
    public static function adminSub(User $user, $price, $admin_id, $remark = ""){
        $t = \Yii::$app->db->beginTransaction();
        try {

            $desc = "管理员[ID:{$admin_id}]扣减：" . $remark;

            static::change($user, $price, self::TYPE_SUB, "admin", null, $desc);

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

    protected static function change(User $user, $price, $type, $source_type, $source_id, $desc = null){

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
            "created_at"       => time()
        ]);
        if(!$integralLog->save()){
            throw new \Exception(json_encode($integralLog->getErrors()));
        }
    }

    /**
     * 支付话费订单
     * @param AddcreditOrder $addcredit_order
     * @param User $user
     * @param $price
     * @return array
     */
    public static function PhoneBillOrderPaySub(AddcreditOrder $addcredit_order, User $user, $price)
    {
        $t = \Yii::$app->db->beginTransaction();
        try {
            $desc = "支付话费订单“".$addcredit_order->order_no."”";

            static::change($user, $price, self::TYPE_SUB, "addcredit", $addcredit_order->id, $desc);

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
     * 话费订单退款抵扣券返还
     * @param User $user
     * @param $price
     * @param $source_id
     * @return array
     */
    public static function PhoneBillOrderRefundAdd(User $user, $price, $source_id){
        $t = \Yii::$app->db->beginTransaction();
        try {
            $desc = "话费订单[Detail Id:".$source_id."]退款退还红包券";

            static::change($user, $price, self::TYPE_ADD, "addcredit_refund", $source_id, $desc);

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
}