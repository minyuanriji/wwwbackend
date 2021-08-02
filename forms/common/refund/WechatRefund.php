<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 微信退款
 * Author: zal
 * Date: 2020-06-28
 * Time: 14:16
 */

namespace app\forms\common\refund;

use app\core\payment\PaymentException;
use app\logic\CommonLogic;
use app\models\PaymentOrder;
use app\models\PaymentRefund;
use app\logic\OptionLogic;
use app\models\Option;
use EasyWeChat\Factory;
use jianyan\easywechat\Wechat;
use yii\db\Exception;

class WechatRefund extends BaseRefund
{
    /**
     * @param \app\models\PaymentRefund $paymentRefund
     * @param \app\models\PaymentOrderUnion $paymentOrderUnion
     * @param $refund_account
     * @return bool|mixed
     * @throws PaymentException
     */
    public function refund($paymentRefund, $paymentOrderUnion,$refund_account = null)
    {

        $t = \Yii::$app->db->beginTransaction();
        try {
            $config = [];
            $payment = OptionLogic::get(Option::NAME_PAYMENT, \Yii::$app->mall->id, Option::GROUP_APP);

            if (!empty($payment) && $payment["wechat_status"] == 1) {
                $config = [
                    'app_id'     => $payment['wechat_app_id'],
                    'mch_id'     => $payment['wechat_mch_id'],
                    'key'        => $payment['wechat_pay_secret'],
                    'cert_path'  => $payment['wechat_cert_pem_path'],
                    'key_path'   => $payment['wechat_key_pem_path'],
                    'sandbox'    => false
                ];
            }
            

            //$wechatPay = Factory::payment($config);
            /** @var Wechat $wechat */
            //$wechat = \Yii::$app->wechat;
            //$wechatPay = $wechat->payment;
            $wechatPay = Factory::payment($config);
            //var_dump($config);exit;
            // 微信退款
            $data = [];
            if(!empty($refund_account)){
                $data["refund_account"] = $refund_account;
            }
            //
            //var_dump($paymentRefund->out_trade_no."-".$paymentRefund->order_no."-".($paymentOrderUnion->amount * 100)."-".($paymentRefund->amount * 100));exit;
            $result = $wechatPay->refund->byOutTradeNumber($paymentRefund->out_trade_no,$paymentRefund->order_no,$paymentOrderUnion->amount * 100,$paymentRefund->amount * 100,$data);
            
            \Yii::warning("wechatRefund refund result=".var_export($result,true));
            if($result["return_code"] == "SUCCESS"){
                if($result["result_code"] == "SUCCESS"){
                    $paymentRefund->is_pay = PaymentRefund::YES;
                    $paymentRefund->pay_type = PaymentRefund::PAY_TYPE_WECHAT;
                    if (!$paymentRefund->save()) {
                        throw new Exception($this->responseErrorMsg($paymentRefund));
                    }
                }else{
                    if (isset($result['err_code']) && $result['err_code'] == 'NOTENOUGH') {
                        // 交易未结算资金不足，请使用可用余额退款
                        return $this->refund($paymentRefund, $paymentOrderUnion, 'REFUND_SOURCE_RECHARGE_FUNDS');
                    }
                    throw new \Exception($result["err_code_des"]);
                }
            }else{
                throw new \Exception($result["return_msg"]);
            }
            $t->commit();
            return true;
        }catch (\Exception $e) {
            \Yii::error("wechatRefund refund error=".CommonLogic::getExceptionMessage($e));
            $t->rollBack();
            throw new PaymentException($e->getMessage());
        }
    }
}
