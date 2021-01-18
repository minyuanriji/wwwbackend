<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 交易基础类
 * Author: zal
 * Date: 2020-04-16
 * Time: 10:45
 */

namespace app\forms\common\transfer;


use app\models\BaseModel;
use app\models\PaymentTransfer;
use app\models\User;

abstract class BaseTransfer extends BaseModel
{
    /**
     * @param PaymentTransfer $paymentTransfer
     * @param User $user
     * @return mixed
     */
    abstract public function transfer($paymentTransfer, $user);
}
