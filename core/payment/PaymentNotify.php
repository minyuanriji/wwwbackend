<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 支付通知类
 * Author: zal
 * Date: 2020-04-16
 * Time: 10:45
 */


namespace app\core\payment;


use yii\base\Component;


abstract class PaymentNotify extends Component
{
    /**
     * @param PaymentOrder $paymentOrder
     * @return mixed
     */
    abstract public function notify($paymentOrder);
}
