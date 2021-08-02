<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 签到插件核心
 * Author: zal
 * Date: 2020-04-20
 * Time: 14:10
 */

namespace app\plugins\sign_in;

use app\helpers\PluginHelper;
use app\plugins\sign_in\forms\common\Common;
use app\plugins\sign_in\forms\common\CommonTemplate;

class Plugin extends \app\plugins\Plugin
{
    /**
     * 插件唯一id，小写英文开头，仅限小写英文、数字、下划线
     * @return string
     */
    public function getName()
    {
        return 'sign_in';
    }

    /**
     * 插件显示名称
     * @return string
     */
    public function getDisplayName()
    {
        return '签到插件';
    }


    /**
     * 获取插件菜单列表
     * @return array
     */
    public function getMenus()
    {
        return [
            [
                'name' => '签到设置',
                'route' => 'plugin/sign_in/mall/index/index',
                'icon' => 'el-icon-star-on',
            ],
            [
                'name' => '签到记录',
                'route' => 'plugin/sign_in/mall/index/record',
                'icon' => 'el-icon-star-on',
                'action' => [
                    [
                        'name' => '详情页',
                        'route' => 'plugin/sign_in/mall/index/user',
                    ],
                ]
            ],
            [
                'name' => '签到规则',
                'route' => 'plugin/sign_in/mall/index/agreement',
                'icon' => 'el-icon-star-on',
            ],
        ];
    }

    /**
     * 插件的小程序端配置，小程序端可使用getApp().config(e => { e.plugin.xxx });获取配置，xxx为插件唯一id
     * @return array
     */
    public function getAppConfig()
    {
        $imageBaseUrl = PluginHelper::getPluginBaseAssetsUrl($this->getName()) . '/img';
        return [
            'app_image' => [
                'sign_in' => $imageBaseUrl . '/check-in.png',
                'get' => $imageBaseUrl . '/get.png',
                'getRed' => $imageBaseUrl . '/getRed.png',
                'over' => $imageBaseUrl . '/over.png',
                'success' => $imageBaseUrl . '/success.png',
                'top_bg' => $imageBaseUrl . '/top-bg.png',
            ]
        ];
    }

    /**
     * 获取插件入口路由
     * @return string|null
     */
    public function getIndexRoute()
    {
        return 'plugin/sign_in/mall/index/index';
    }

    /**
     * 插件可共用的跳转链接
     * @return array
     */
    public function getPickLink()
    {
        $iconBaseUrl = PluginHelper::getPluginBaseAssetsUrl($this->getName()) . '/img/pick-link';

        return [
            [
                'key' => 'sign_in',
                'name' => '签到',
                'open_type' => 'navigate',
                'icon' => $iconBaseUrl . '/icon-check-in.png',
                'value' => '/plugins/sign_in/sign_in',
            ],
        ];
    }

    public function getUserInfo($user)
    {
        $common = Common::getCommon(\Yii::$app->mall);
        $signInUser = $common->getSignInUser($user);
        $todayAward = $common->getAwardConfigNormal();
        return [
            'sign_in' => [
                'continue' => isset($signInUser->continue) ? $signInUser->continue : 0,
                'total' => isset($signInUser->total) ? $signInUser->total : 0,
                'todayAward' => $todayAward ? $todayAward->getExplain() : ''
            ]
        ];
    }

    public function templateList()
    {
        return [
            'sign_in_tpl' => CommonTemplate::class,
        ];
    }

    public function getLogo()
    {
        // TODO: Implement getLogo() method.
    }

    public function getPriceTypeName($price_log_id=0)
    {
        // TODO: Implement getPriceTypeName() method.
    }
}
