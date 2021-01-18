<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * Created by PhpStorm
 * Author: ganxiaohao
 * Date: 2020-05-08
 * Time: 15:41
 */

namespace app\plugins\boss;


use app\handlers\BaseHandler;
use app\plugins\boss\forms\common\Common;
use app\plugins\boss\handlers\HandlerRegister;
use app\plugins\boss\models\boss;
use app\plugins\boss\models\BossPriceLogType;

class Plugin extends \app\plugins\Plugin
{
    public function getIsSetToQuickMenu()
    {
        //这里去缓存里面查询
        return false; // TODO: Change the autogenerated stub
    }

    //为主菜单提供的菜单
    public function getMenuForMainMenu()
    {
        return [
            'key' => $this->getName(),
            'name' => '股东分红',
            'route' => $this->getIndexRoute(),
            'children' => $this->getMenus(),
            'icon' => 'statics/img/mall/nav/finance.png',
            'icon_active' => 'statics/img/mall/nav/finance-active.png',
        ]; // TODO: Change the autogenerated stub
    }

    public function getMenus()
    {
        return [
            [
                'name' => '股东',
                'route' => 'plugin/boss/mall/boss/index',
                'icon' => 'el-icon-setting',

            ],
            [
                'name' => '基础配置',
                'route' => 'plugin/boss/mall/boss/setting',
                'icon' => 'el-icon-setting',

            ],
            [
                'name' => '股东等级',
                'route' => 'plugin/boss/mall/level/index',
                'icon' => 'el-icon-setting',
                'action' => [
                    [
                        'name' => '股东等级编辑(S|U)',
                        'route' => 'plugin/boss/mall/level/edit',
                    ],
                ]
            ],
            [
                'name' => '提成明细',
                'route' => 'plugin/boss/mall/boss/income-list',
                'icon' => 'el-icon-setting',
                'action' => [
                    [
                        'name' => '详情(S|U)',
                        'route' => 'plugin/boss/mall/boss/income-detail',
                    ],
                ]
            ],
        ];
    }

    public function getIndexRoute()
    {
        return 'plugin/boss/mall/boss/index';
    }

    public function getStatisticsMenus()
    {
        return [
            'name' => $this->getDisplayName(),
            'key' => $this->getName(),
            'route' => 'mall/boss-statistics/index',
        ];
    }

    /**
     * 插件唯一id，小写英文开头，仅限小写英文、数字、下划线
     * @return string
     */
    public function getName()
    {
        return 'boss';
    }

    /**
     * 插件显示名称
     * @return string
     */
    public function getDisplayName()
    {
        return '股东分红';
    }


    public function getIsPlatformPlugin()
    {
        return true;
    }

    public function handler()
    {
        $register = new HandlerRegister();
        $HandlerClasses = $register->getHandlers();
        foreach ($HandlerClasses as $HandlerClass) {
            $handler = new $HandlerClass();
            if ($handler instanceof BaseHandler) {
                /** @var BaseHandler $handler */
                $handler->register();
            }
        }
        return $this;
    }

    /**
     * 获取股东信息
     * @param \app\models\User $user
     * @return array
     * @throws \Exception
     */
    public function getUserInfo($user)
    {
        $common = Common::getCommon(\Yii::$app->mall);
        /** @var boss $bosss */
        $bosss = $common->getBossInfo($user);
        return [
            'boss' => [
                'total_price' => $bosss["total_price"] ?? '0.00',
                'frozen_price' => $bosss["frozen_price"] ?? '0.00',
                'yesterday_price' => $bosss["yesterday_price"] ?? '0.00',
                'level_name' => $bosss["level_name"],
                'sign' => $this->getName(),
                'plugin_name' => $this->getDisplayName(),
                'logo' => $this->getLogo()
            ]
        ];
    }

    /**
     * 获取经销记录列表
     * @param $user
     * @param $params
     * @return array
     * @throws \Exception
     */
    public function getList($user, $params)
    {
        $common = Common::getCommon(\Yii::$app->mall);
        $returnData = $common->getBossLogList($user, $params);
        return $returnData;
    }

    /**
     * 获取经销订单
     * @param $order
     * @return array
     * @throws \Exception
     */
    public function getBossOrderList($order)
    {
        $common = Common::getCommon(\Yii::$app->mall);
        $returnData = $common->getBossOrderList($order);
        return $returnData;
    }

    public function getLogo()
    {
        // TODO: Implement pluginLogo() method.

        return '';
    }


    public function getPriceTypeName($price_log_id = 0)
    {
        // TODO: Implement getPriceTypeName() method.
        $price_type = BossPriceLogType::findOne(['price_log_id' => $price_log_id]);
        if ($price_type) {
            return BossPriceLogType::getTypeName($price_type->type);
        }
        return '未知类型';
    }
}
