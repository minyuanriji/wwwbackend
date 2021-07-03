<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 微信配置
 * Author: xuyaoxiang
 * Date: 2020/10/14
 * Time: 9:58
 */

namespace app\services\wechat;

use app\logic\OptionLogic;
use app\models\Option;
use app\models\Wechat;

class WechatParmasService
{
    /**
     * 数据库获取微信app_id
     * @param $mall_id
     */
    public function setWechatParmas($mall_id)
    {
        if (!$mall_id) {
            return;
        }
        $info = Wechat::findOne(['mall_id' => $mall_id, 'is_delete' => 0]);
        if ($info) {
            \Yii::$app->params['wechatConfig'] = [
                'app_id'  => $info->app_id,
                'secret'  => $info->secret,
                'token'   => $info->token,
                'aes_key' => $info->aes_key,
            ];
        }
        $payment = OptionLogic::get(Option::NAME_PAYMENT, $mall_id, Option::GROUP_APP);
        if (!empty($payment) && $payment["wechat_status"] == 1) {
            \Yii::$app->params['wechatPaymentConfig'] = [
                'app_id'     => $payment['wechat_app_id'],
                'mch_id'     => $payment['wechat_mch_id'],
                'key'        => $payment['wechat_pay_secret'],
                'cert_path'  => $payment['wechat_cert_pem_path'],
                'key_path'   => $payment['wechat_key_pem_path'],
                'notify_url' => ''//回调地址
            ];
        }
        $mpwx = OptionLogic::get(Option::NAME_MPWX, $mall_id, Option::GROUP_APP);
        if (!empty($mpwx)) {
            \Yii::$app->params['wechatMiniProgramConfig'] = [
                'app_id'     => $mpwx['app_id'],
                'secret'     => $mpwx['secret'],
                'mch_id'     => $mpwx['mch_id'],
                'key'        => $mpwx['pay_secret'],
                'cert_path'  => $mpwx['cert_pem_path'],
                'key_path'   => $mpwx['key_pem_path'],
                'notify_url' => ''//回调地址
            ];
        }
    }
}