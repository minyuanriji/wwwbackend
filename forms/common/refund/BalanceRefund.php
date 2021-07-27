<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 余额退款
 * Author: zal
 * Date: 2020-06-28
 * Time: 14:16
 */

namespace app\forms\common\refund;


use app\core\payment\PaymentException;
use app\models\User;
use yii\db\Exception;

class BalanceRefund extends BaseRefund
{
    /**
     * @param \app\models\PaymentRefund $paymentRefund
     * @param \app\models\PaymentOrderUnion $paymentOrderUnion
     * @return bool|mixed
     * @throws PaymentException
     */
    public function refund($paymentRefund, $paymentOrderUnion)
    {
        $t = \Yii::$app->db->beginTransaction();
        try {
            $user = User::find()->where(['id' => $paymentRefund->user_id, 'mall_id' => $paymentRefund->mall_id])
                ->with('userInfo')->one();
            \Yii::$app->currency->setUser($user)->balance->refund(floatval($paymentRefund->amount), '订单退款');
            $paymentRefund->is_pay = 1;
            $paymentRefund->pay_type = 3;
            if (!$paymentRefund->save()) {
                throw new Exception($this->responseErrorMsg($paymentRefund));
            }
            $t->commit();
            return true;
        } catch (Exception $e) {
            $t->rollBack();
            throw new PaymentException($e->getMessage());
        }
    }
}
