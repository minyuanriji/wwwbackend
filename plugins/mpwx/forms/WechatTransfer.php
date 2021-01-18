<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 微信转账
 * Author: zal
 * Date: 2020-04-18
 * Time: 15:50
 */

namespace app\plugins\mpwx\forms;


use app\core\payment\PaymentException;
use app\forms\common\transfer\BaseTransfer;
use app\plugins\mpwx\Plugin;
use luweiss\Wechat\WechatException;

class WechatTransfer extends BaseTransfer
{
    /**
     * @param \app\models\PaymentTransfer $paymentTransfer
     * @param \app\models\User $user
     * @return bool
     * @throws PaymentException
     */
    public function transfer($paymentTransfer, $user)
    {
        $t = \Yii::$app->db->beginTransaction();
        try {
            $plugin = new Plugin();
            $wechatPay = $plugin->getWechatPay();
            $result = $wechatPay->transfers([
                'partner_trade_no' => $paymentTransfer->order_no,
                'openid' => $user->userInfo->platform_user_id,
                'amount' => $paymentTransfer->amount * 100,
                'desc' => '转账',
            ]);
            $paymentTransfer->is_pay = 1;
            if (!$paymentTransfer->save()) {
                throw new \Exception($this->responseErrorMsg($paymentTransfer));
            }
            $t->commit();
            return true;
        } catch (WechatException $e) {
            $t->rollBack();
            throw new PaymentException($e->getRaw()['err_code_des']);
        } catch (\Exception $e) {
            $t->rollBack();
            throw new PaymentException($e->getMessage());
        }
    }
}
