<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * Created by PhpStorm
 * Author: zal
 * Date: 2020-04-01
 * Time: 21:49
 */

namespace app\forms\common\coupon;


use app\models\Coupon;
use app\models\CouponAutoSend;
use app\models\UserCouponAuto;

class UserCouponAutoCommon extends UserCouponData
{
    public $coupon;
    public $autoSend;
    public $userCoupon;

    public function __construct(Coupon $coupon, CouponAutoSend $autoSend)
    {
        $this->coupon = $coupon;
        $this->autoSend = $autoSend;
    }

    /**
     * @return bool
     * @throws \yii\db\Exception
     */
    public function save()
    {
        if ($this->check($this->coupon)) {
            $this->coupon->updateCount(1, 'sub');
        }
        $userCouponCenter = new UserCouponAuto();
        $userCouponCenter->user_coupon_id = $this->userCoupon->id;
        $userCouponCenter->auto_coupon_id = $this->autoSend->id;
        return $userCouponCenter->save();
    }
}