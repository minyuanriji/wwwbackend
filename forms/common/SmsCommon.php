<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 短信
 * Author: zal
 * Date: 2020-04-23
 * Time: 17:36
 */

namespace app\forms\common;

use app\models\BaseModel;
use app\plugins\Plugin;

class SmsCommon extends BaseModel
{
    public $mall;

    public static function getCommon($mall = null)
    {
        $model = new self();
        if (!$mall) {
            $mall = \Yii::$app->mall;
        }
        $model->mall = $mall;
        return $model;
    }

    public function getSetting()
    {
        $setting = [
            'new_user' => [
                'title' => '新用户注册',
                'content' => '例如：模板内容：你有一个新用户注册,用户名:${nickname}.。',
                'support_mch' => true,
                'loading' => false,
                'variable' => [
                    [
                        'key' => 'template_variable',
                        'value' => '模板变量',
                        'desc' => '例如：模板内容：你有一个新用户注册,用户名:${nickname}.。，则只需填写nickname'
                    ]
                ]
            ],
            'order' => [
                'title' => '订单支付提醒设置',
                'content' => '例如：模板内容：您有一条新的订单，订单号：${order_sn}.',
                'support_mch' => true,
                'loading' => false,
                'variable' => [
                    [
                        'key' => 'template_variable',
                        'value' => '模板变量',
                        'desc' => '例如：模板内容：您有一个新的订单，订单号：${order_sn}，则只需填写order'
                    ]
                ]
            ],
            'order_refund' => [
                'title' => '订单售后提醒设置',
                'content' => '例如：模板内容：你有一个订单申请售后,订单号:${order_sn},请及时处理。',
                'support_mch' => true,
                'loading' => false,
                'variable' => [
                    [
                        'key' => 'template_variable',
                        'value' => '模板变量',
                        'desc' => '例如：模板内容：你有一个订单申请售后,订单号:${order_sn},请及时处理。，则只需填写order_sn'
                    ]
                ]
            ],
            'captcha' => [
                'title' => '通用验证码设置',
                'content' => '例如：模板内容：您的验证码为89757，请勿告知他人。',
                'support_mch' => false,
                'loading' => false,
                'variable' => [
                    [
                        'key' => 'template_variable',
                        'value' => '模板变量',
                        'desc' => '例如：模板内容：您的验证码为${code}，请勿告知他人。，则只需填写code'
                    ]
                ]
            ],
            'user_registration' => [
                'title' => '用户注册验证码',
                'content' => '例如：模板内容：您正在申请手机注册，验证码为:${code}，请勿泄露他人。',
                'support_mch' => false,
                'loading' => false,
                'variable' => [
                    [
                        'key' => 'template_variable',
                        'value' => '模板变量',
                        'desc' => '例如：模板内容：您正在申请手机注册，验证码为：${code}，请勿泄露他人！'
                    ]
                ]
            ],
        ];
        try {
            $plugins = \Yii::$app->plugin->list;
            foreach ($plugins as $plugin) {
                $pluginClass = 'app\\plugins\\' . $plugin->name . '\\Plugin';
                /** @var Plugin $pluginObject */
                if (!class_exists($pluginClass)) {
                    continue;
                }
                $object = new $pluginClass();
                if (method_exists($object, 'getSmsSetting')) {
                    $setting = array_merge($setting, $object->getSmsSetting());
                }
            }
        } catch (\Exception $exception) {
        }
        return $setting;
    }
}
