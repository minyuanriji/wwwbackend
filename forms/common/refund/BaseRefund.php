<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 余额退款基础类
 * Author: zal
 * Date: 2020-06-28
 * Time: 14:16
 */

namespace app\forms\common\refund;


use app\models\BaseModel;
use app\models\PaymentOrderUnion;
use app\models\PaymentRefund;

abstract class BaseRefund extends BaseModel
{
    /**
     * @param PaymentRefund $paymentRefund
     * @param PaymentOrderUnion $paymentOrderUnion
     * @return mixed
     */
    abstract public function refund($paymentRefund, $paymentOrderUnion);
}
