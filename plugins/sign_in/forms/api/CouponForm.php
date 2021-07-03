<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2020/8/22
 * Time: 18:09
 */


namespace app\plugins\sign_in\forms\api;


class CouponForm extends ApiModel
{

    public function rules()
    {

    }

    /**
     * @Author: 广东七件事 ganxiaohao
     * @Date: 2020-05-06
     * @Time: 17:37
     * @Note: 领取优惠券
     * @param Coupon $coupon
     * @param UserCouponData $class
     * @param string $content
     * @return bool
     * @throws Exception
     */
    public function receive(Coupon $coupon, UserCouponData $class, $content)
    {
        $t = \Yii::$app->db->beginTransaction();
        $userCoupon = new UserCoupon();
        $userCoupon->mall_id = $this->mall->id;
        $userCoupon->user_id = $this->user->id;
        $userCoupon->coupon_id = $coupon->id;
        $userCoupon->coupon_min_price = $coupon->min_price;
        $userCoupon->sub_price = $coupon->sub_price;
        $userCoupon->discount = $coupon->discount;
        $userCoupon->discount_limit = $coupon->discount_limit;
        $userCoupon->type = $coupon->type;
        $userCoupon->is_use = 0;
        $userCoupon->receive_type = $content;
        if ($coupon->expire_type == 1) {
            $time = time();
            $userCoupon->begin_at = $time;
            $userCoupon->end_at = $time + $coupon->expire_day * 86400;
        } else {
            $userCoupon->begin_at = $coupon->begin_at;
            $userCoupon->end_at = $coupon->end_at;
        }
        $cat = $coupon->cat;
        $goods = $coupon->goods;
        $arr = ArrayHelper::toArray($coupon);
        $arr['cat'] = ArrayHelper::toArray($cat);
        $arr['goods'] = ArrayHelper::toArray($goods);
        $userCoupon->coupon_data = json_encode($arr, JSON_UNESCAPED_UNICODE);
        if ($userCoupon->save()) {
            $class->userCoupon = $userCoupon;
            if ($class->save()) {
                $t->commit();
                return true;
            } else {
                $t->rollBack();
                return false;
            }
        } else {
            $t->rollBack();
            throw new Exception(isset($userCoupon->errors) ? current($userCoupon->errors)[0] : '数据异常！');
        }
    }



}