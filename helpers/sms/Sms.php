<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 短信信息
 * Author: zal
 * Date: 2020-04-18
 * Time: 15:11
 */

namespace app\helpers\sms;

use app\logic\AppConfigLogic;
use app\logic\CommonLogic;
use app\models\ErrorLog;
use app\models\ValidateCode;
use app\models\ValidateCodeLog;
use app\plugins\stock\helpers\StockFillMessage;
use Overtrue\EasySms\EasySms;
use Overtrue\EasySms\Exceptions\NoGatewayAvailableException;

class Sms
{
    public $config;
    public $smsConfig;
    public $easySms;
    public $minutes = 1;// 短信发送间隔（分钟）
    public static $validityMinutes = 5;// 验证码有效时间（分钟）

    public function __construct()
    {
        $this->config = [
            // HTTP 请求的超时时间（秒）
            'timeout' => 5.0,

            // 默认发送配置
            'default' => [
                // 网关调用策略，默认：顺序调用
                'strategy' => \Overtrue\EasySms\Strategies\OrderStrategy::class,

                // 默认可用的发送网关
                'gateways' => [
                    'aliyun',
                ],
            ],
            // 可用的网关配置
            'gateways' => [
                'errorlog' => [
                    'file' => '/runtime/easy-sms.log',
                ],
                //...
            ],
        ];

        $this->smsConfig = AppConfigLogic::getSmsConfig();

        // 阿里云短信配置
        if ($this->smsConfig['platform'] == 'aliyun') {
            $this->config['gateways']['aliyun'] = [
                'access_key_id' => $this->smsConfig['access_key_id'],
                'access_key_secret' => $this->smsConfig['access_key_secret'],
                'sign_name' => $this->smsConfig['template_name'],
            ];
        }

        $this->easySms = new EasySms($this->config);
    }

    /**
     * 发送短信验证码
     * @param string $mobile
     * @return bool
     * @throws NoGatewayAvailableException
     * @throws \Exception
     */
    public function sendCaptcha(string $mobile)
    {
        $validateDate = strtotime(date('Y-m-d H:i:s', time() - $this->minutes * 60));
        $ValidateCode = ValidateCode::find()->where([
            'target' => $mobile,
            'is_validated' => ValidateCode::IS_VALIDATED_FALSE
        ])->andWhere(['>', 'created_at', $validateDate])->one();

        if ($ValidateCode) {
            throw new \Exception('操作频繁,请一分钟后再重试');
        }

        try {
            $captcha = (string)mt_rand(100000, 999999);
            if (isset(\Yii::$app->params['sms_phone_list']) && \Yii::$app->params['sms_phone_list']) {
                $sms_phone_list = \Yii::$app->params['sms_phone_list'];
            } else {
                $sms_phone_list = [];
            }
            if (!in_array($mobile, $sms_phone_list)) {
                $message = new CaptchaMessage($captcha, $this->smsConfig['captcha']);
                $results = $this->easySms->send($mobile, $message);
                $ValidateCode = new ValidateCode();
                $ValidateCode->target = $mobile;
                $ValidateCode->code = $captcha;
                $res = $ValidateCode->save();
                $this->saveValidateCodeLog($mobile, $message->getContent() . $captcha);
                return true;
            } else {
                $ValidateCode = new ValidateCode();
                $ValidateCode->target = $mobile;
                $ValidateCode->code = $captcha;
                $res = $ValidateCode->save();
                $this->saveValidateCodeLog($mobile, '测试手机号：'.$mobile . '验证码:' . $captcha . '不发送短信！');
                throw new \Exception($captcha);
            }
        } catch (NoGatewayAvailableException $e) {
            \Yii::error('短信发送失败:' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * 发送新订单短信通知
     * @param array $mobile [157.., 183..]
     * @param $orderNo
     * @return bool
     * @throws NoGatewayAvailableException
     * @throws \Exception
     */
    public function sendOrderMessage(array $mobile, $order_id)
    {
        if (count($mobile) != count($mobile, 1)) {
            throw new \Exception('手机号数组格式错误,请传入一维数组');
        }

        $message = new NewOrderMessage($order_id, $this->smsConfig['order']);

        foreach ($mobile as $item) {
            try {
                $this->easySms->send($item, $message);
                $this->saveValidateCodeLog($item, $message->getContent() . $order_id);
            } catch (\Exception $e) {
                \Yii::error('短信发送失败:' . $e->getMessage());
            }
        }
    }

    /**
     * 发送新用户短信通知
     * @param array $mobile [157.., 183..]
     * @param $orderNo
     * @return bool
     * @throws NoGatewayAvailableException
     * @throws \Exception
     */
    public function sendNewUserMessage(array $mobile, $nickname)
    {
        if (count($mobile) != count($mobile, 1)) {
            throw new \Exception('手机号数组格式错误,请传入一维数组');
        }

        $message = new NewUserMessage($nickname, $this->smsConfig['new_user']);

        foreach ($mobile as $item) {
            try {
                $this->easySms->send($item, $message);
                $this->saveValidateCodeLog($item, $message->getContent());
            } catch (\Exception $e) {
                \Yii::error('短信发送失败:' . $e->getMessage());
            }
        }
    }

    /**
     * 发送订单退款短信通知
     * @param array $mobile [157.., 183..]
     * @param $orderNo
     * @return bool
     * @throws NoGatewayAvailableException
     * @throws \Exception
     */
    public function sendOrderRefundMessage(array $mobile, $order_id)
    {
        if (count($mobile) != count($mobile, 1)) {
            throw new \Exception('手机号数组格式错误,请传入一维数组');
        }

        try {
            $message = new OrderRefundMessage($order_id, $this->smsConfig['order_refund']);
            foreach ($mobile as $item) {
                $mobile = (string)$item;
                $this->easySms->send($mobile, $message);
                $this->saveValidateCodeLog($mobile, $message->getContent() . $order_id);
            }
            return true;
        } catch (NoGatewayAvailableException $e) {
            \Yii::error('短信发送失败:' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * 保存短信发送日志
     * @param $target
     * @param $content
     * @return bool
     */
    public function saveValidateCodeLog($target, $content)
    {
        $log = new ValidateCodeLog();
        $log->target = $target;
        $log->content = $content;
        $log->created_at = time();
        $res = $log->save();

        return $res;
    }

    /**
     * 检测短信验证码是否有效
     * @param $mobile
     * @param $code
     * @return bool
     */
    public static function checkValidateCode($mobile, $code)
    {
        $validateDate = time() - self::$validityMinutes * 60;
        $ValidateCode = ValidateCode::find()->where([
            'target' => $mobile,
            'code' => $code,
            'is_validated' => ValidateCode::IS_VALIDATED_FALSE
        ])->andWhere(['>', 'created_at', $validateDate])->one();
        if ($ValidateCode) {
            return true;
        }

        return false;
    }

    /**
     * 将验证码状态更新为已使用
     * @param $mobile
     * @param $code
     * @return bool
     * @throws \Exception
     */
    public static function updateCodeStatus($mobile, $code)
    {
        //测试验证码1
//         return true;
        $ValidateCode = ValidateCode::find()->where([
            'target' => $mobile,
            'code' => $code,
        ])->one();
        if (!$ValidateCode) {
            throw new \Exception('验证码不存在');
        }

        $ValidateCode->is_validated = ValidateCode::IS_VALIDATED_TRUE;
        $res = $ValidateCode->save();
        if (!$res) {
            throw new \Exception('验证码可用状态更新失败');
        }

        return true;
    }
}
