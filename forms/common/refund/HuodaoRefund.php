<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 货到付款退款
 * Author: zal
 * Date: 2020-06-28
 * Time: 14:16
 */

namespace app\forms\common\refund;

use app\core\payment\PaymentException;

class HuodaoRefund extends BaseRefund
{
    /**
     * @param \app\models\PaymentRefund $paymentRefund
     * @param \app\models\PaymentOrderUnion $paymentOrderUnion
     * @return bool|mixed
     * @throws PaymentException
     */
    public function refund($paymentRefund, $paymentOrderUnion)
    {
        $paymentRefund->is_pay = 1;
        $paymentRefund->pay_type = 2;
        if (!$paymentRefund->save()) {
            throw new PaymentException($this->responseErrorMsg($paymentRefund));
        }
        return true;
    }
}
