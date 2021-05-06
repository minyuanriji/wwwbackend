<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * Created by PhpStorm
 * Author: ganxiaohao
 * Date: 2020-05-05
 * Time: 9:52
 */

namespace app\forms\api\coupon;


use app\core\ApiCode;
use app\forms\common\coupon\CouponCommon;
use app\forms\common\coupon\UserCouponCenter;
use app\models\BaseModel;
use app\models\UserCoupon;

class CouponReceiveForm extends BaseModel
{
    public $coupon_id;
    public $receive_type;
    public function rules()
    {
        return [
            ['coupon_id', 'required'],
            [['coupon_id', 'receive_type'], 'integer'],
        ];
    }
    public function receive()
    {
        if (!$this->validate()) {
            return $this->returnApiResultData();
        }
        if (!$this->receive_type) {
            $this->receive_type = 0;
        }
        try {
            $common = new CouponCommon(['coupon_id' => $this->coupon_id], false);
            $common->user = \Yii::$app->user->identity;
            $coupon = $common->getDetail();
            if ($coupon->is_delete == 1) {
                return $this->returnApiResultData(ApiCode::CODE_FAIL, '优惠券不存在');
            }
            $count = $common->checkReceive($coupon->id);
            if ($count > 0) {
                return $this->returnApiResultData(ApiCode::CODE_FAIL, UserCoupon::$RECEIVE_TYPES[$this->receive_type]);
            } else {
                $class = new UserCouponCenter($coupon, $common->user);
                if ($common->receive($coupon, $class, UserCoupon::$RECEIVE_TYPES[$this->receive_type])) {
                    return $this->returnApiResultData(ApiCode::CODE_SUCCESS, '领取成功');
                } else {
                    return $this->returnApiResultData(ApiCode::CODE_FAIL, '优惠券已领完');
                }
            }
        } catch (\Exception $e) {
            return $this->returnApiResultData(ApiCode::CODE_FAIL, $e->getMessage());
        }
    }
}