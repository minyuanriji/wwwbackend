<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 用户中心表单
 * Author: zal
 * Date: 2020-04-13
 * Time: 10:16
 */

namespace app\forms\mall\shop;

use app\core\ApiCode;
use app\logic\AppConfigLogic;
use app\models\BaseModel;

class UserCenterForm extends BaseModel
{
    public function getDetail()
    {
        $userCenter = AppConfigLogic::getUserCenter();

        if (!isset($userCenter['account_bar']['status'])) {
            $userCenter['account_bar'] = $this->getDefault()['account_bar'];
        } else {
            $accountBar = $this->getDefault()['account_bar'];
            foreach ($accountBar as $key => $item) {
                if (!isset($userCenter['account_bar'][$key])) {
                    $userCenter['account_bar'][$key] = $item;
                }
            }
        }
        //足迹兼容旧商城
        if (!isset($userCenter['is_foot_bar_status'])) {
            $userCenter['is_foot_bar_status'] = $this->getDefault()['is_foot_bar_status'];
        }
        if (!isset($userCenter['foot_bar'])) {
            $userCenter['foot_bar'] = $this->getDefault()['foot_bar'];
        }


        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '请求成功',
            'data' => [
                'detail' => $userCenter,
            ]
        ];
    }

    public function getDefault()
    {
        $iconUrlPrefix = \Yii::$app->request->hostInfo . \Yii::$app->request->baseUrl .
            '/statics/img/mall/user-center/';
        return [
            'is_account_status' => '1',
            'is_show_user_tool' => '1',
            'user_tool_menu_title' => '我的功能',
            'user_tool_menu_style' => '1',
            'is_menu_status' => '1',
            'menu_title' => '营销功能',
            'is_order_bar_status' => '1',
            'is_foot_bar_status' => '1',
            'menu_style' => '1',
            'top_style' => '1',
            'top_pic_url' => $iconUrlPrefix . 'img-user-bg.png',
            'member_pic_url' => $iconUrlPrefix . 'icon-member.png',
            'member_bg_pic_url' => $iconUrlPrefix . 'card-member-0.png',
            'style_bg_pic_url' => $iconUrlPrefix . 'img-user-bg.png',
            'account' => [ // TODO 好像是废弃不用了
                [
                    'icon_url' => $iconUrlPrefix . 'icon-wallet.png',
                    'name' => '我的钱包',
                ],
                [
                    'icon_url' => $iconUrlPrefix . 'icon-integral.png',
                    'name' => '积分',
                ],
                [
                    'icon_url' => $iconUrlPrefix . 'icon-balance.png',
                    'name' => '余额',
                ],
            ],
            'menus' => [],
            'user_tool_menus' => [],
            'order_bar' => [
                [
                    'icon_url' => $iconUrlPrefix . 'icon-order-0.png',
                    'name' => '待付款',
                ],
                [
                    'icon_url' => $iconUrlPrefix . 'icon-order-1.png',
                    'name' => '待发货',
                ],
                [
                    'icon_url' => $iconUrlPrefix . 'icon-order-2.png',
                    'name' => '待收货',
                ],
                [
                    'icon_url' => $iconUrlPrefix . 'icon-order-3.png',
                    'name' => '待评价',
                ],
                [
                    'icon_url' => $iconUrlPrefix . 'icon-order-4.png',
                    'name' => '售后',
                ],
            ],
            'foot_bar' => [
                [
                    'icon_url' => $iconUrlPrefix . 'favorite.png',
                    'name' => '我的收藏',
                ],
                [
                    'icon_url' => $iconUrlPrefix . 'foot.png',
                    'name' => '我的足迹',
                ],
            ],
            'account_bar' => [
                'status' => '1',
                'integral' => [
                    'status' => '1',
                    'text' => '积分',
                    'icon' => $iconUrlPrefix . 'icon-integral.png',
                ],
                'balance' => [
                    'status' => '1',
                    'text' => '余额',
                    'icon' => $iconUrlPrefix . 'icon-balance.png',
                ],
                'coupon' => [
                    'status' => '1',
                    'text' => '优惠券',
                    'icon' => $iconUrlPrefix . 'icon-coupon.png',
                ],
                'card' => [
                    'status' => '1',
                    'text' => '卡券',
                    'icon' => $iconUrlPrefix . 'icon-card.png',
                ],
            ],
        ];
    }
}
