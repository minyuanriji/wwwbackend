<?php

namespace app\mch\forms;


class Menus
{

    /**
     * @Note: 商户菜单
     * @return array
     */
    public static function getMenus(){
        $menus = [
            [
                'name' => '概况',
                'route' => '',
                'key' => 'overview',
                'icon' => 'statics/img/mall/nav/statics.png',
                'icon_active' => 'statics/img/mall/nav/statics-active.png',
                'icon_font' => 'el-icon-stopwatch',
                'children' => [
                    [
                        'name' => '概况',
                        'route' => '',
                        'children' => [
                            [
                                'name' => '概况',
                                'route' => 'mch/overview/index',
                                'action' => [

                                ],
                            ],

                        ]
                    ],

                ]
            ],
            [
                'name' => '店铺',
                'key' => 'shop-setting',
                'route' => '',
                'icon' => 'statics/img/mall/nav/mall-manage.png',
                'icon_active' => 'statics/img/mall/nav/mall-manage-active.png',
                'icon_font' => 'el-icon-s-shop',
                'children' => [
                    [
                        'name' => '店铺信息',
                        'route' => 'mch/mch/edit-store',
                        'action' => []
                    ],
                    [
                        'name' => '首页装修',
                        'route' => 'mch/home-page/diy',
                        'action' => [
                            [
                                'name' => '首页装修',
                                'is_menu' => false,
                                'route' => 'mch/home-page/diy',
                            ]
                        ]
                    ],
                ]
            ],
            [
                'name' => '商品',
                'key' => 'goods',
                'route' => 'mch/goods/index',
                'icon' => 'statics/img/mall/nav/goods.png',
                'icon_active' => 'statics/img/mall/nav/goods-active.png',
                'icon_font' => 'el-icon-s-goods',
                'children' => [
                    [
                        'name' => '我的爆品',
                        'route' => 'mch/baopin/index',
                        'action' => [
                            
                        ]
                    ],
                    [
                        'name' => '商品列表',
                        'route' => 'mch/goods/index',
                        'action' => [
                            [
                                'name' => '商品删除',
                                'route' => 'mch/goods/delete'
                            ],
                            [
                                'name' => '商品批量操作删除',
                                'route' => 'mch/goods/batch-destroy'
                            ],
                            [
                                'name' => '商品(上架|下架)',
                                'route' => 'mch/goods/goods-up-down'
                            ],
                            [
                                'name' => '商品(S|U)',
                                'route' => 'mch/goods/edit',
                            ],
                            [
                                'name' => '商品导出',
                                'route' => 'mch/goods/export-goods-list',
                            ],
                        ]
                    ],
                    [
                        'name' => '类目设置',
                        'route' => 'mch/cat/index',
                        'action' => []
                    ]/*,
                    [
                        'name' => '运费规则',
                        'route' => 'mch/postage-rules/index',
                        'action' => []
                    ]*/
                ],
            ],
            [
                'name' => '订单',
                'key' => 'order',
                'route' => 'mch/order/index',
                'icon' => 'statics/img/mall/nav/order.png',
                'icon_active' => 'statics/img/mall/nav/order-active.png',
                'icon_font' => 'el-icon-s-order',
                'children' => [
                    [
                        'name' => '订单管理',
                        'route' => 'mch/order/index',
                        'action' => [
                            [
                                'name' => '订单移入回收站',
                                'route' => 'mch/order/edit'
                            ],
                            [
                                'name' => '订单添加备注',
                                'route' => 'mch/order/seller-comments'
                            ],
                            [
                                'name' => '订单发货',
                                'route' => 'mch/order/send'
                            ],
                            [
                                'name' => '订单打印',
                                'route' => 'mch/order/print'
                            ],
                            [
                                'name' => '订单申请状态',
                                'route' => 'mch/order/apply-delete-status'
                            ],
                            [
                                'name' => '订单货到付款状态',
                                'route' => 'mch/order/confirm'
                            ],
                            [
                                'name' => '订单详情',
                                'route' => 'mch/order/detail'
                            ],
                        ]
                    ],
                    [
                        'name' => '售后订单',
                        'route' => 'mch/order/refund',
                        'action' => [
                            [
                                'name' => '售后详情',
                                'route' => 'mch/order/refund-detail'
                            ],
                        ]
                    ],
                    [
                        'name' => '退货地址',
                        'route' => 'mall/refund-setting/index',
                    ],
                ],
            ],
            [
                'name' => '设置',
                'key' => 'setting',
                'route' => '',
                'icon' => 'statics/img/mall/nav/setting.png',
                'icon_active' => 'statics/img/mall/nav/setting-active.png',
                'icon_font' => 'el-icon-s-tools',
                'children' => [
                    [
                        'name' => '账号设置',
                        'route' => 'mch/mch/password',
                    ],
                ],
            ],
        ];

        return $menus;
    }
}
