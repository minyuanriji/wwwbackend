<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 用户优惠券相关逻辑处理
 * Author: zal
 * Date: 2020-05-05
 * Time: 16:36
 */

namespace app\logic;

use app\core\exceptions\OrderException;
use app\models\UserCoupon;

class UserCouponLogic
{
    /**
     * 获取用户优惠券可优惠金额
     * @param UserCoupon $userCoupon
     * @param $totalGoodsOriginalPrice
     * @return float|int
     * @throws OrderException
     */
    public static function getDiscountAmount($userCoupon,$totalGoodsOriginalPrice){
        $sub = 0;
        if ($userCoupon->type == UserCoupon::TYPE_DISCOUNT) { // 折扣券
            if ($userCoupon->discount <= 0 || $userCoupon->discount >= 1) {
                throw new OrderException('优惠券折扣信息错误，折扣范围必须是`0 < 折扣 < 1`。');
            }
            $discount = $totalGoodsOriginalPrice * (1 - $userCoupon->discount);
            \Yii::warning('userCouponLogic getDiscountAmount userCoupon->discount='.$userCoupon->discount.';totalGoodsOriginalPrice='.$totalGoodsOriginalPrice.';discount=' . $discount);
            $sub = !empty($userCoupon->discount_limit) && ($userCoupon->discount_limit < $discount) ? $userCoupon->discount_limit : $discount;
        } elseif ($userCoupon->type == UserCoupon::TYPE_REDUCTION) { // 满减券
            if ($userCoupon->sub_price <= 0) {
                throw new OrderException('优惠券优惠信息错误，优惠金额必须大于0元。');
            }
            $sub = $userCoupon->sub_price;
        }
        \Yii::warning('userCouponLogic getDiscountAmount sub='.$sub);
        return $sub;
    }
}