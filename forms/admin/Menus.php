<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 菜单
 * Author: zal
 * Date: 2020-04-09
 * Time: 11:16
 */

namespace app\forms\admin;

use phpDocumentor\Reflection\Types\Self_;

class Menus
{
    /**
     * 只允许 超级管理员 访问的商城路由KEY
     */
    const MALL_SUPER_ADMIN_KEY = [
        'course_setting',
        'permission_manage',
        'app_manage',
        'upload_admin',
        'add_account',
        'register_audit',
        'system_setting',
        'db_manage',
        'account_list',
        'overrun',
        'base_setting',
        'queue_service',
    ];

    /**
     * 只允许 多商户 访问的路由KEY
     */
    const MALL_MCH_KEY = [
        'mall/mch/setting',
        'mall/mch/manage',
        'mall/mch/account-log',
        'mall/mch/cash-log',
        'mall/mch/order-close-log',
    ];

    /**
     * @Author: 广东七件事 zal
     * @Date: 2020-04-09
     * @Time: 11:49
     * @Note: 商城主菜单
     * @param bool $isPluginMenus
     * @return array
     */
    public static function getMallMenus($isPluginMenus = false)
    {
        $menu_item = [
            'name' => '',
            'key' => '',
            'route' => '',
            'icon' => '',
            'icon_active' => '',
            'children' => [],
        ];
        $mallMenus = [
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
                                'route' => 'mall/overview/index',
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
                        'name' => '店铺设计',
                        'route' => '',
                        'children' => [
                            [
                                'name' => '首页装修',
                                'route' => 'mall/home-page/diy',
                                'action' => [
                                    [
                                        'name' => '首页装修',
                                        'is_menu' => false,
                                        'route' => 'mall/home-page/diy',
                                    ]
                                ]
                            ],
                            [
                                'name' => '底部导航',
                                'route' => 'mall/tabbar/setting',
                                'action' => [
                                    [
                                        'name' => '恢复默认设置',
                                        'is_menu' => false,
                                        'route' => 'mall/tabbar/default'
                                    ]
                                ]
                            ],
                            [
                                'name' => '全局设置',
                                'route' => 'mall/color/setting',
                                'action' => [
                                    [
                                        'name' => '恢复默认设置',
                                        'is_menu' => false,
                                        'route' => 'mall/color/default'
                                    ]
                                ]
                            ],
                            [
                                'name' => '用户中心',
                                'route' => 'mall/user-center/setting',
                            ],
                            [
                                'name' => '下单表单',
                                'route' => 'mall/order-form/list',
                                'action' => [
                                    [
                                        'name' => '下单表单编辑',
                                        'route' => 'mall/order-form/setting'
                                    ]
                                ]
                            ],
                            [
                                'name' => '自定义海报',
                                'route' => 'mall/poster/setting',
                            ],
                        ]
                    ],
                   [
                       'name' => 'diy装修',
                       'route' => '',
                       'children' => [
                           [
                               'name' => '模板管理',
                               'route' => 'plugin/diy/mall/template/index',
                               'icon' => 'el-icon-star-on',
                               'action' => [
                                   [
                                       'name' => '模板编辑',
                                       'route' => 'plugin/diy/mall/template/edit',
                                   ],
                               ]
                           ],
                        //    [
                        //        'name' => '模板市场',
                        //        'route' => 'plugin/diy/mall/market/list',
                        //        'icon' => 'el-icon-star-on',
                        //    ],
                           [
                               'name' => '自定义页面',
                               'route' => 'plugin/diy/mall/page/index',
                               'icon' => 'el-icon-star-on',
                               'action' => [
                                   [
                                       'name' => '自定义页面编辑',
                                       'route' => 'plugin/diy/mall/page/edit',
                                   ],
                               ]
                           ],
                        //    [
                        //        'name' => '授权页面',
                        //        'route' => 'plugin/diy/mall/page/auth',
                        //        'icon' => 'el-icon-star-on',
                        //    ],
                        //    [
                        //        'name' => '表单提交信息',
                        //        'route' => 'plugin/diy/mall/page/info',
                        //        'icon' => 'el-icon-star-on',
                        //    ],
                       ]
                   ],
                    [
                        'name' => '页面管理',
                        'route' => '',
                        'children' => [
                            [
                                'name' => '页面管理',
                                'route' => 'mall/app-page/index',
                            ],
                            [
                                'name' => '页面标题',
                                'route' => 'mall/page-title/setting',
                            ],
                            [
                                'key' => 'copyright',
                                'name' => '版权设置',
                                'route' => 'mall/copyright/setting',
                            ],
                        ]
                    ],
                    [
                        'name' => '内容管理',
                        'route' => 'mall/article/index',
                        'icon' => 'statics/img/mall/nav/content.png',
                        'children' => [
                            [
                                'name' => '文章',
                                'route' => 'mall/article/index',
                                'action' => [
                                    [
                                        'name' => '文章(S|U)',

                                        'route' => 'mall/article/edit',
                                    ],
                                    [
                                        'name' => '文章删除',
                                        'route' => 'mall/article/delete',
                                    ]
                                ],
                            ],
                            [
                                'key' => 'topic',
                                'name' => '专题分类',
                                'route' => 'mall/topic-type/index',
                                'action' => [
                                    [
                                        'name' => '专题分类删除',
                                        'route' => 'mall/topic-type/delete'
                                    ],
                                    [
                                        'name' => '专题分类(S|U)',
                                        'route' => 'mall/topic-type/edit',
                                    ]
                                ],
                            ],
                            [
                                'key' => 'topic',
                                'name' => '专题',
                                'route' => 'mall/topic/index',
                                'action' => [
                                    [
                                        'name' => '专题删除',
                                        'route' => 'mall/topic/delete'
                                    ],
                                    [
                                        'name' => '专题(S|U)',
                                        'route' => 'mall/topic/edit',
                                    ]
                                ],
                            ],
                            [
                                'key' => 'video',
                                'name' => '视频',
                                'route' => 'mall/video/index',
                                'action' => [
                                    [
                                        'name' => '视频(S|U)',
                                        'route' => 'mall/video/edit',
                                    ],
                                    [
                                        'name' => '视频删除',
                                        'route' => 'mall/video/delete',
                                    ]
                                ]
                            ],
//                            [
//                                'name' => '门店管理',
//                                'route' => 'mall/store/index',
//                                'action' => [
//                                    [
//                                        'name' => '门店删除',
//                                        'route' => 'mall/store/destroy',
//                                    ],
//                                    [
//                                        'name' => '设置默认门店',
//                                        'route' => 'mall/store/default',
//                                    ],
//                                    [
//                                        'name' => '门店(S|U)',
//                                        'route' => 'mall/store/edit',
//                                    ]
//                                ]
//                            ],
                        ],
                    ],
                ]
            ],
            [
                'name' => '商品',
                'key' => 'goods',
                'route' => 'mall/goods/index',
                'icon' => 'statics/img/mall/nav/goods.png',
                'icon_active' => 'statics/img/mall/nav/goods-active.png',
                'icon_font' => 'el-icon-s-goods',
                'children' => [
                    [
                        'name' => '商品列表',
                        'route' => 'mall/goods/index',
                        'action' => [
                            [
                                'name' => '商品删除',
                                'route' => 'mall/goods/delete'
                            ],
                            [
                                'name' => '商品批量操作删除',
                                'route' => 'mall/goods/batch-destroy'
                            ],
                            [
                                'name' => '商品(上架|下架)',
                                'route' => 'mall/goods/goods-up-down'
                            ],
                            [
                                'name' => '商品(S|U)',
                                'route' => 'mall/goods/edit',
                            ],
                            [
                                'name' => '商品导出',
                                'route' => 'mall/goods/export-goods-list',
                            ],
                        ]
                    ],
                    [
                        'name' => '商品分类',
                        'route' => 'mall/cat/index',
                        'action' => [
                            [
                                'name' => '商品分类删除',
                                'route' => 'mall/cat/cat-destroy'
                            ],
                            [
                                'name' => '商品分类(S|U)',
                                'route' => 'mall/cat/edit',
                            ]
                        ]
                    ],
//                    [
//                        'name' => '分类页面样式',
//                        'route' => 'mall/cat/style',
//                    ],


                    [
                        'name' => '推荐设置',
                        'route' => 'mall/goods/recommend-setting',
                    ],
                    [
                        'name' => '商品服务',
                        'route' => 'mall/service/index',
                        'action' => [
                            [
                                'name' => '商品服务删除',
                                'route' => 'mall/service/destroy'
                            ],
                            [
                                'name' => '商品服务(S|U)',

                                'route' => 'mall/service/edit',
                            ]
                        ]
                    ],
                    [
                        'name' => '商品标签',
                        'route' => 'mall/goods/label',
                        'action' => [
                            [
                                'name' => '商品标签删除',
                                'route' => 'mall/service/delete'
                            ],
                            [
                                'name' => '商品标签(S|U)',
                                'route' => 'mall/goods/label-edit',
                            ]
                        ]
                    ],
                    [
                        'name' => '物流设置',
                        'route' => '',
                        'children' => [
                            [
                                'name' => '规则设置',
                                'route' => 'mall/setting/rule',
                                'action' => [
                                    [
                                        'name' => '运费规则',
                                        'route' => 'mall/postage-rule/index',
                                    ],
                                    [
                                        'name' => '运费规则状态(U)',
                                        'route' => 'mall/postage-rule/edit/status',
                                    ],
                                    [
                                        'name' => '运费规则删除',
                                        'route' => 'mall/postage-rule/destroy',
                                    ],
                                    [
                                        'name' => '运费规则(S|U)',
                                        'route' => 'mall/postage-rule/edit'
                                    ],
                                    [
                                        'name' => '包邮规则',
                                        'route' => 'mall/free-delivery-rules/index',
                                    ],
                                    [
                                        'name' => '包邮规则删除',
                                        'route' => 'mall/free-delivery-rules/delete',
                                    ],
                                    [
                                        'name' => '包邮规则(S|U)',
                                        'route' => 'mall/free-delivery-rules/edit'
                                    ],
                                    [
                                        'name' => '起送规则',
                                        'route' => 'mall/offer-price/index',
                                    ],
                                ]
                            ],
                            [
                                'name' => '退货地址',
                                'route' => 'mall/refund-setting/index',
                                'action' => [
                                    [
                                        'name' => '退货地址编辑',
                                        'route' => 'mall/refund-setting/edit',
                                    ]
                                ],
                            ],
                            [
                                'name' => '区域购买',
                                'route' => 'mall/area-limit/index',
                            ],
                            [
                                'name' => '电子面单',
                                'route' => 'mall/express/index',
                                'action' => [
                                    [
                                        'name' => '电子面单删除',
                                        'route' => 'mall/express/delete',
                                    ],
                                    [
                                        'name' => '电子面单打印(S|U)',
                                        'route' => 'mall/express/edit',
                                    ]
                                ]
                            ],
                            [
                                'name' => '小票打印',
                                'route' => 'mall/printer/index',
                                'action' => [
                                    [
                                        'name' => '小票打印设置',
                                        'route' => 'mall/printer/setting',
                                    ],
                                    [
                                        'name' => '小票打印编辑',
                                        'route' => 'mall/printer/edit',
                                    ]
                                ],
                            ],
                        ],
                    ],
                    [
                        'name' => '商品回收站',
                        'route' => 'mall/goods/recycle-bin',
                        'action' => [

                        ]
                    ],
                ],
            ],
            [
                'name' => '订单',
                'key' => 'order',
                'route' => 'mall/order/index',
                'icon' => 'statics/img/mall/nav/order.png',
                'icon_active' => 'statics/img/mall/nav/order-active.png',
                'icon_font' => 'el-icon-s-order',
                'children' => [
                    [
                        'name' => '订单管理',
                        'route' => 'mall/order/index',
                        'action' => [
                            [
                                'name' => '订单移入回收站',
                                'route' => 'mall/order/edit'
                            ],
                            [
                                'name' => '订单添加备注',
                                'route' => 'mall/order/seller-comments'
                            ],
                            [
                                'name' => '订单发货',
                                'route' => 'mall/order/send'
                            ],
                            [
                                'name' => '订单打印',
                                'route' => 'mall/order/print'
                            ],
                            [
                                'name' => '订单申请状态',
                                'route' => 'mall/order/apply-delete-status'
                            ],
                            [
                                'name' => '订单货到付款状态',
                                'route' => 'mall/order/confirm'
                            ],
                            [
                                'name' => '订单详情',
                                'route' => 'mall/order/detail'
                            ],
                        ]
                    ],
                    [
                        'name' => '售后订单',
                        'route' => 'mall/order/refund',
                        'action' => [
                            [
                                'name' => '售后详情',
                                'route' => 'mall/order/refund-detail'
                            ],
                        ]
                    ],
                    [
                        'name' => '评价管理',
                        'route' => 'mall/order-comments/index',
                        'action' => [
                            [
                                'name' => '订单评价删除',
                                'route' => 'mch/comment/delete-status'
                            ],
                            [
                                'name' => '订单评价隐藏',
                                'route' => 'mch/comment/hide-status'
                            ],
                            [
                                'name' => '订单评价回复',
                                'route' => 'mall/order-comments/reply',
                            ],
                            [
                                'name' => '订单评价(S|U)',
                                'route' => 'mall/order-comments/edit',
                            ],
                            [
                                'name' => '订单评价(S|U)',
                                'route' => 'mall/order-comment-templates、index',
                            ],
                        ]
                    ],
                    [
                        'name' => '批量发货',
                        'route' => 'mall/order/batch-send',
                        'action' => [
                            [
                                'name' => '批量发货',
                                'route' => 'mmall/order/batch-send'
                            ],
                        ]
                    ],
                    [
                        'name' => '核销记录',
                        'route' => 'mall/order-clerk/store',
                        'action' => [
                            [
                                'name' => '补货管理',
                                'route' => 'mall/order-clerk/index',
                            ]
                        ]
                    ],
                ],
            ],
            [
                'name' => '会员',
                'key' => 'user-manager',
                'route' => 'mall/user/index',
                'icon' => 'statics/img/mall/nav/user.png',
                'icon_active' => 'statics/img/mall/nav/user-active.png',
                'icon_font' => 'el-icon-user-solid',
                'children' => [
                    [
                        'name' => '用户管理',
                        'route' => 'mall/user/index',
                        'children' => [
                            [
                                'name' => '用户列表',
                                'route' => 'mall/user/index',
                                'action' => [
                                    [
                                        'name' => '用户(设置/取消核销员)',
                                        'route' => 'mall/user/clerk-edit'
                                    ],
                                    [
                                        'name' => '用户核销员列表',
                                        'route' => 'mall/user/clerk'
                                    ],
                                    [
                                        'name' => '用户列表',
                                        'route' => 'mall/user/get-user'
                                    ],
                                    [
                                        'name' => '用户删除',
                                        'route' => 'mall/user/del'
                                    ],
                                    [
                                        'name' => '用户积分充值',
                                        'route' => 'mall/user/rechange'
                                    ],
                                    [
                                        'name' => '用户金额充值',
                                        'route' => 'mall/user/recharge-money'
                                    ],
                                    [
                                        'name' => '用户卡券删除',
                                        'route' => 'mall/user/coupon-del'
                                    ],
                                    [
                                        'name' => '用户卡券',
                                        'route' => 'mall/user/coupon',
                                    ],
                                    [
                                        'name' => '用户编辑',
                                        'route' => 'mall/user/edit',
                                    ],
                                    [
                                        'name' => '用户余额',
                                        'route' => 'mall/user/recharge-money-log'
                                    ],
                                    [
                                        'name' => '积分充值记录',
                                        'route' => 'mall/user/rechange-log',
                                    ],
                                ]
                            ],

                            [
                                'name' => '会员等级',
                                'route' => 'mall/member-level/index',
                                'action' => [
                                    [
                                        'name' => '会员等级状态(启用|禁用)',
                                        'route' => 'mall/member-level/status'
                                    ],
                                    [
                                        'name' => '会员等级删除',
                                        'route' => 'mall/member-level/destroy'
                                    ],
                                    [
                                        'name' => '用户会员等级(S|U)',
                                        'route' => 'mall/member-level/edit',
                                    ],
                                ]
                            ],
                            [
                                'name' => '关系设置',
                                'route' => 'mall/user/relation-edit',
                            ],
                            [
                                'name' => '会员标签',
                                'route' => 'mall/setting/tag',
                                'action' => [
                                    [
                                        'name' => '会员标签列表',
                                        'route' => 'mall/setting/tag-list'
                                    ],
                                ]
                            ],
                            [
                                'name' => '注册协议',
                                'route' => 'mall/setting/register-agree',
                                'action' => [
                                    [
                                        'name' => '会员注册协议',
                                        'route' => 'mall/setting/register-agree'
                                    ],
                                ]
                            ],
                            [
                                'name' => '标签列表',
                                'route' => 'mall/setting/tag-list',
                                'action' => [
                                    [
                                        'name' => '会员标签列表',
                                        'route' => 'mall/setting/tag-list'
                                    ],
                                ]
                            ],
                            [
                                'name' => '优惠券管理',
                                'route' => 'mall/coupon/index',
                                'action' => [
                                    [
                                        'name' => '优惠券分类删除',
                                        'route' => 'mall/coupon/delete-cat'
                                    ],
                                    [
                                        'name' => '优惠券删除',
                                        'route' => 'mall/coupon/delete'
                                    ],
                                    [
                                        'name' => '优惠券发放',
                                        'route' => 'mall/coupon/send'
                                    ],
                                ]
                            ],
                            [
                                'name' => '优惠券自动发放',
                                'route' => 'mall/coupon-auto-send/index',
                                'action' => [
                                    [
                                        'name' => '优惠券自动发放',
                                        'route' => 'mall/coupon/auto-send-edit'
                                    ],
                                    [
                                        'name' => '优惠券自动发放方案删除',
                                        'route' => 'mall/coupon/auto-send-delete'
                                    ],
                                    [
                                        'name' => '自动发放编辑',
                                        'route' => 'mall/coupon/auto-send-edit'
                                    ]
                                ]
                            ],
                        ]
                    ],
                    [
                        'name' => '分销',
                        'route' => '',
                        'children' => [
                            [
                                'name' => '分销商',
                                'route' => 'plugin/distribution/mall/distribution/index'
                            ],
                            [
                                'name' => '基础配置',
                                'route' => 'plugin/distribution/mall/setting/index'
                            ],
                            [
                                'name' => '申请列表',
                                'route' => 'plugin/distribution/mall/distribution/apply'
                            ],
                            [
                                'name' => '分销等级',
                                'route' => 'plugin/distribution/mall/level/index'
                            ],
                            [
                                'name' => '分销提成',
                                'route' => 'plugin/distribution/mall/distribution/income-list'
                            ]
                        ]
                    ],
                    [
                        'name' => '经销',
                        'route' => '',
                        'children' => [
                            [
                                'name' => '经销商',
                                'route' => 'plugin/agent/mall/agent/index'
                            ],
                            [
                                'name' => '基础配置',
                                'route' => 'plugin/agent/mall/agent/setting'
                            ],
                            [
                                'name' => '经销商等级',
                                'route' => 'plugin/agent/mall/level/index'
                            ],
                            [
                                'name' => '提成明细',
                                'route' => 'plugin/agent/mall/agent/income-list'
                            ]
                        ]
                    ],
                    [
                        'name' => '积分券充值卡',
                        'route' => 'plugin/integral_card/admin/card/index',

                    ],
                    [
                        'name' => '积分自动发放计划',
                        'route' => '',
                        'children' => [
                            [
                                'name' => '积分管理列表',
                                'route' => 'mall/integral/index',
                                'action' => [
                                ]
                            ],
                        ]
                    ],
                ],
            ],
            [
                'name' => '资产',
                'key' => 'finance',
                'route' => 'mall/finance/index',
                'icon' => 'statics/img/mall/nav/finance.png',
                'icon_active' => 'statics/img/mall/nav/finance-active.png',
                'icon_font' => 'el-icon-s-finance',
                'children' => [
                    [
                        'name' => '用户财务',
                        'route' => 'mall/finance/index',
                    ],
                    [
                        'name' => '财务概况',
                        'route' => 'plugin/finance_analysis/mall/finance/analysis',
                    ],
                    [
                        'name' => '余额记录',
                        'route' => 'mall/finance/balance-log',
                    ],
                    [
                        'name' => '余额收支',
                        'route' => 'mall/user/balance-log',
                    ],
                    [
                        'name' => '积分记录',
                        'route' => 'mall/finance/score-log',
                    ],
                    [
                        'name' => '金豆记录',
                        'route' => 'mall/finance/integral-log',
                    ],
                    [
                        'name' => '红包记录',
                        'route' => 'plugin/shopping_voucher/mall/shopping-voucher-log/list',
                    ],
                    [
                        'name' => '用户提现',
                        'route' => 'mall/finance/cash',
                    ],
                    [
                        'name' => '商户提现',
                        'route' => 'mall/mch-cash/index',
                    ],
                    [
                        'name' => '商户待结算',
                        'route' => 'mall/mch-price-log/index',
                    ],
                    [
                        'name' => '用户收益',
                        'route' => 'mall/finance/income-log',
                    ],
                    [
                        'name' => '财务设置',
                        'route' => 'mall/finance/setting',
                    ],
                    [
                        'name' => '卡券管理',
                        'route' => 'mall/finance/integral-plan',
                        'children' => [
                            [
                                'name' => '购物卡券',
                                'route' => 'mall/finance/integral-plan',
                            ],
                            [
                                'name' => '积分卡券',
                                'route' => 'mall/finance/score-plan',
                            ],
                        ]
                    ],
                ],
            ],
            [
                'name' => '分佣',
                'key' => 'commission',
                'route' => '',
                'icon' => 'statics/img/mall/nav/Sub-Commission.png',
                'icon_active' => 'statics/img/mall/nav/Sub-Commission.png',
                'icon_font' => 'el-icon-s-tools',
                'children' => [
                    [
                        'name' => '商品消费分佣记录',
                        'route' => 'mall/commission/goods-consume-log',
                    ],
                    [
                        'name' => '门店推荐分佣记录',
                        'route' => 'mall/commission/store-recommend-log',
                    ],
                    [
                        'name' => '门店结账分佣记录',
                        'route' => 'mall/commission/store-scan-code-log',
                    ],
                    [
                        'name' => '酒店推荐分佣记录',
                        'route' => 'mall/commission/hotel-recommend-log',
                    ],
                    [
                        'name' => '酒店消费分佣记录',
                        'route' => 'mall/commission/hotel-scan-code-log',
                    ],
                    [
                        'name' => '话费推荐分佣记录',
                        'route' => 'mall/commission/addcredit-recommend-log',
                    ],
                    [
                        'name' => '话费充值分佣记录',
                        'route' => 'mall/commission/addcredit-scan-code-log',
                    ],
                ],
            ],
            [
                'key' => 'course',
                'name' => '教程管理',
                'icon' => 'statics/img/mall/nav/study.png',
                'icon_font' => 'el-icon-share',
                'route' => 'mall/tutorial/index',
                'children' => [
                    [
                        'name' => '操作教程',
                        'route' => 'mall/tutorial/index',
                    ],
                    [
                        'key' => 'course_setting',// 超级管理员显示
                        'name' => '教程设置',
                        'route' => 'mall/tutorial/setting',
                    ],
                ]
            ],
            [
                'name' => '系统工具',
                'key' => 'sys-tool',
                'icon' => 'statics/img/mall/nav/tool.png',
                'icon_active' => 'statics/img/mall/nav/tool-active.png',
                'icon_font' => 'el-icon-s-tools',
                'route' => '',
                'children' => [
                    [
                        'key' => 'base_setting',
                        'ignore' => ['ind',],
                        'name' => '基础设置',
                        'route' => 'mall/we7/base-setting',
                        'params' => [
                            '_layout' => 'mall',
                        ],
                    ],
                    [
                        'key' => 'attachment',
                        'ignore' => ['ind',],
                        'name' => '账户上传管理',
                        'route' => 'admin/setting/attachment',
                        'params' => [
                            '_layout' => 'mall',
                        ],
                    ],
                    [
                        'key' => 'permission_manage',// 超级管理员
                        'ignore' => ['ind',],
                        'name' => '权限分配',
                        'route' => 'mall/we7/auth',
                    ],
                    [
                        'key' => 'small_procedure',
                        'name' => '小程序管理',
                        'ignore' => ['ind',],
                        'route' => 'admin/mall/index',
                        'params' => [
                            '_layout' => 'mall',
                        ],
                    ],
//                    [
//                        'name' => '缓存',
//                        'route' => 'admin/cache/clean',
//                        'params' => [
//                            '_layout' => 'mall',
//                        ],
//                    ],
                    [
                        'key' => 'queue_service',
                        'name' => '队列服务',
                        'ignore' => ['ind',],
                        'route' => 'admin/setting/queue-service',
                        'params' => [
                            '_layout' => 'mall',
                        ],
                    ],
                    [
                        'key' => 'upload_admin',// 独立版 微擎线下版 超级管理员
                        'name' => '更新',
                        'ignore' => ['ind', 'we7'],
                        'route' => 'admin/update/index',
                        'params' => [
                            '_layout' => 'mall',
                        ],
                    ],
                    [
                        'key' => 'overrun',
                        'name' => '超限设置',
                        'route' => 'admin/setting/overrun',
                        'ignore' => ['ind'],
                        'icon' => 'icon-qinglihuancun',
                        'params' => [
                            '_layout' => 'mall',
                        ],
                    ],
//                    [
//                        'name' => 'v3商城导入',
//                        'route' => 'mall/import/index',
//                        'params' => [
//                            '_layout' => 'mall',
//                        ],
//                    ],
                ],
            ],
            [
                'name' => '数据',
                'route' => '',
                'key' => 'statistics',
                'icon' => 'statics/img/mall/nav/statics.png',
                'icon_active' => 'statics/img/mall/nav/statics-active.png',
                'icon_font' => 'el-icon-pie-chart',
                'children' => [
                    [
                        'name' => '数据统计',
                        'route' => '',
                        'children' => [
                            [
                                'name' => '数据概况',
                                'route' => 'mall/data-statistics/index',
                                'action' => [
                                    [
                                        'name' => '商品购买力TOP排行',
                                        'route' => 'mall/data-statistics/goods_top',
                                    ],
                                    [
                                        'name' => '用户购买力TOP排行',
                                        'route' => 'mall/data-statistics/users_top',
                                    ],
                                ],
                            ],
                            [
                                'name' => '商品统计',
                                'route' => 'mall/data-statistics/goods-statistics',
                                'action' => [
                                ],
                            ],
//                            [
//                                'name' => '分销排行',
//                                'route' => 'mall/score-statistics/index',
//                            ],
                        ]
                    ],
                    [
                        'name' => '销售报表',
                        'route' => '',
                        'children' => [
                            [
                                'name' => '销售统计',
                                'route' => 'mall/order-statistics/index',
                            ],
                            /*   [
                                   'name' => '门店',
                                   'route' => 'mall/order-statistics/shop',
                               ]*/
                        ]
                    ],
                    [
                        'name' => '大屏设置',
                        'route' => 'mall/statistics/index',
                    ],
                ]
            ],
            [
                'name' => '营销',
                'key' => 'plugin-marketing',
                'route' => '',
                'icon' => 'statics/img/mall/nav/marketing.png',
                'icon_active' => 'statics/img/mall/nav/marketing.png',
                'icon_font' => 'el-icon-data-analysis',
                'children' => [
                    [
                        'name' => '微信公众号',
                        'route' => 'mall/wechat/edit',
                        'children' => [
                            [
                                'key' => 'wechat_edit',
                                'name' => '公众号设置',
                                'route' => 'mall/wechat/edit',
                            ],
                            [
                                'key' => 'wechat_menus',
                                'name' => '自定义菜单',
                                'route' => 'mall/wechat/menus',
                            ],
                            [
                                'key' => 'wechat_reply',
                                'name' => '自动回复',
                                'route' => 'mall/wechat/reply-rule',
                            ],
                            [
                                'key' => 'wechat_material',
                                'name' => '素材管理',
                                'route' => 'mall/wechat/material',
                            ],
                            [
                                'key' => 'wechat_article',
                                'name' => '图文素材',
                                'route' => 'mall/wechat/article',
                            ],
                        ],
                    ],
                    [
                        'name' => '微信小程序',
                        'route' => 'plugin/mpwx/mall/config/setting',
                        'children' => [
                            [
                                'key' => 'mpwx_setting',
                                'name' => '小程序设置',
                                'route' => 'plugin/mpwx/mall/config/setting',
                            ],
                            [
                                'key' => 'mpwx_app_upload',
                                'name' => '小程序发布',
                                'route' => 'plugin/mpwx/mall/app-upload',
                            ],
                            [
                                'key' => 'mpwx_no_mch',
                                'name' => '单商户小程序发布',
                                'route' => 'plugin/mpwx/mall/app-upload/no-mch',
                            ],
                            [
                                'key' => 'mpwx_template_msg',
                                'name' => '群发模板消息',
                                'route' => 'plugin/mpwx/mall/template-msg/send',
                            ],
                        ]
                    ],
                    [
                        'name' => '直播管理',
                        'route' => 'mall/live/index',
                        'children' => [
                            [
                                'name' => '直播间管理',
                                'route' => 'mall/live/index',
                            ],
                            [
                                'name' => '直播商品',
                                'route' => 'mall/live/goods',
                            ],
                        ]
                    ],
                ],
            ],
            [
                'name' => '应用',
                'key' => 'plugin-center',
                'route' => 'mall/plugin/index',
                'icon' => 'statics/img/mall/nav/plugins.png',
                'icon_active' => 'statics/img/mall/nav/plugins.png',
                'icon_font' => 'el-icon-menu',
                'children' => [
                    [
                        'name' => '插件中心',
                        'key' => 'plugins',
                        'route' => 'mall/plugin/index',
                        'action' => [
                            [
                                'name' => 'NotInstallList',
                                'route' => 'mall/plugin/not-install-list',
                            ],
                            [
                                'name' => 'Detail',
                                'route' => 'mall/plugin/detail',
                            ],
                            [
                                'name' => 'Buy',
                                'route' => 'mall/plugin/buy',
                            ],
                            [
                                'name' => 'Pay',
                                'route' => 'mall/plugin/pay',
                            ],
                            [
                                'name' => 'Download',
                                'route' => 'mall/plugin/download',
                            ],
                            [
                                'name' => 'Install',
                                'route' => 'mall/plugin/install',
                            ],
                            [
                                'name' => 'Uninstall',
                                'route' => 'mall/plugin/uninstall',
                            ],
                            [
                                'name' => 'CheckUpdateList',
                                'route' => 'mall/plugin/check-update-list',
                            ],
                        ],
                    ],
                ]
            ],
            [
                'name' => '商城',
                'route' => '',
                'key' => 'mirror',
                'icon' => 'statics/img/mall/nav/mall.png',
                'icon_active' => 'statics/img/mall/nav/mall.png',
                'icon_font' => 'el-icon-pie-chart',
                'children' => [
                    [
                        'name' => '子账号列表',
                        'route' => 'mall/mirror/son-account-list',
                    ],
                    [
                        'name' => '子商城列表',
                        'route' => 'mall/mirror/son-mall-list',
                    ],
                ]
            ],
            [
                'name' => '设置',
                'key' => 'setting',
                'route' => '',
                'icon' => 'statics/img/mall/nav/setting.png',
                'icon_active' => 'statics/img/mall/nav/setting-active.png',
                'icon_font' => 'el-icon-s-tools',
                'children' => [
//                    [
//                        'key' => 'mch_setting',
//                        'name' => '店铺设置',
//                        'route' => 'mall/mch/setting',
//                    ],
//                    [
//                        'key' => 'mch_manage',
//                        'name' => '店铺管理',
//                        'route' => 'mall/mch/manage',
//                    ],
                    [
                        'name' => '基础设置',
                        'route' => 'mall/setting/setting',
                    ],
                    [
                        'name' => '消息提醒',
                        'route' => 'mall/setting/notice',
                        'action' => [
                            [
                                'name' => '公众号配置',
                                'route' => 'mall/setting/template',
                            ],
                            [
                                'name' => '短信通知',
                                'route' => 'mall/setting/sms',
                            ],
                            [
                                'name' => '邮件通知',
                                'route' => 'mall/setting/mail',
                            ]
                        ]
                    ],
                    [
                        'key' => 'rule_user',// 员工账号不能显示
                        'name' => '员工管理',
                        'icon' => 'statics/img/mall/nav/staff.png',
                        'route' => '',
                        'children' => [
                            [
                                'name' => '基础设置',
                                'icon' => 'icon-manage',
                                'route' => 'mall/role-setting/index',
                            ],
                            [
                                'name' => '角色列表',
                                'icon' => 'icon-manage',
                                'route' => 'mall/role/index',
                                'action' => [
                                    [

                                        'name' => '添加角色',
                                        'route' => 'mall/role/create',
                                    ],
                                    [
                                        'name' => '编辑角色',
                                        'route' => 'mall/role/edit',
                                    ],
                                    [
                                        'name' => '角色(U)',
                                        'route' => 'mall/role/update',
                                    ],
                                    [
                                        'name' => '角色删除',
                                        'route' => 'mall/role/destroy',
                                    ],
                                    [
                                        'name' => '角色(S)',
                                        'route' => 'mall/role/store',
                                    ],
                                ]
                            ],
                            [
                                'name' => '员工列表',
                                'route' => 'mall/role-user/index',
                                'action' => [
                                    [
                                        'name' => '员工(U)',
                                        'route' => 'mall/role-user/update',
                                    ],
                                    [
                                        'name' => '员工删除',
                                        'route' => 'mall/role-user/destroy',
                                    ],
                                    [
                                        'name' => '员工(S)',
                                        'route' => 'mall/role-user/store',
                                    ],
                                    [
                                        'name' => '添加员工',
                                        'route' => 'mall/role-user/create',
                                    ],
                                    [
                                        'name' => '编辑员工',
                                        'route' => 'mall/role-user/edit',
                                    ],
                                ]
                            ],
                            [
                                'name' => '操作记录',
                                'route' => 'mall/role-user/action',
                                'action' => [
                                    [
                                        'name' => '操作详情',
                                        'route' => 'mall/role-user/action-edit',
                                    ],
                                ]
                            ],
                        ]
                    ],

                    [
                        'key' => 'course',
                        'name' => '教程管理',
                        'icon' => 'statics/img/mall/nav/study.png',
                        'route' => 'mall/tutorial/index',
                        'children' => [
                            [
                                'name' => '操作教程',
                                'route' => 'mall/tutorial/index',
                            ],
                            [
                                'key' => 'course_setting',// 超级管理员显示
                                'name' => '教程设置',
                                'route' => 'mall/tutorial/setting',
                            ],
                        ]
                    ],
                    [
                        'key' => 'upload',
                        'name' => '上传设置',
                        'icon' => 'icon-shangchuan',
                        'route' => 'mall/attachment/attachment',
                    ],
                    [
                        'key' => 'attachment',
                        'name' => '素材管理',
                        'icon' => 'icon-shangchuan',
                        'route' => 'mall/attachment/index',
                    ],
                    [
                        'name' => '管理员操作日志',
                        'route' => 'mall/setting/operate-log',
                    ],
                    [
                        'name' => '敏感词过滤日志',
                        'route' => 'mall/sensitive/index',
                        'action' => [
                            [
                                'name' => '商品服务删除',
                                'route' => 'mall/sensitive/destroy'
                            ],
                            [
                                'name' => '商品服务(S|U)',
                                'route' => 'mall/sensitive/edit',
                            ]
                        ]
                    ],
                    [
                        'name' => '用户日志',
                        'route' => 'mall/setting/user-log',
                    ],
                ],
            ],
        ];
        $plugins = \Yii::$app->plugin->list;
        $pluginMenus = [];
        $statisticsMenus = [];
        $platformMenus = [];
        foreach ($plugins as $plugin) {
            $pluginClass = 'app\\plugins\\' . $plugin->name . '\\Plugin';
            /** @var Plugin $object */
            if (!class_exists($pluginClass)) {
                continue;
            }
            $object = new $pluginClass();
            $menus = [];
            if (method_exists($object, 'getMenus') && $isPluginMenus) {
                $menus = $object->getMenus();
                $newMenus = [
                    'name' => $object->getDisplayName(),
                    'icon' => '',
                    'children' => $menus,
                    'route' => isset($menus[0]['route']) ? $menus[0]['route'] : '',
                ];
                $pluginMenus[] = $newMenus;
            }
            if (method_exists($object, 'getStatisticsMenus')) {
                $arr = $object->getStatisticsMenus();
                if (count($arr) > 0) {
                    // TODO 判断children 为了兼容
                    if (isset($arr['children'])) {
                        foreach ($arr['children'] as $child) {
                            $child['key'] = $object->getName();
                            array_push($statisticsMenus, $child);
                        }
                    } else {
                        if (count($arr) == count($arr, 1)) {
                            array_push($statisticsMenus, $arr);
                        } else {
                            foreach ($arr as $aItem) {
                                array_push($statisticsMenus, $aItem);
                            }
                        }
                    }
                }
            }
            //判断是否加入快速访问菜单
            if (method_exists($object, 'getIsSetToQuickMenu')) {
                if ($object->getIsSetToQuickMenu()) {
                    $mainMenu = $object->getMenuForMainMenu();
                    $platformMenus[] = $mainMenu;
                }
            }
        }
        $mallMenus = array_merge($mallMenus, $platformMenus);
        foreach ($mallMenus as &$menu) {
            $menu = self::setExtraMenu($menu, $pluginMenus, $platformMenus, $statisticsMenus, $isPluginMenus);
        }
        unset($menu);
        return $mallMenus;
    }


    public static function setExtraMenu($item, $pluginMenus, $platformMenus, $statisticsMenus, $isPluginMenus)
    {
        if (isset($item['key']) && $item['key'] == 'plugins' && $isPluginMenus) {
            $item['children'] = $pluginMenus;
        }

        if (isset($item['key']) && $item['key'] == 'app-manage') {
            $item['children'] = array_merge($item['children'], $platformMenus);
        }



        if (isset($item['children'])) {
            foreach ($item['children'] as $key => $child) {
                $item['children'][$key] = self::setExtraMenu($child, $pluginMenus, $platformMenus, $statisticsMenus, $isPluginMenus);
            }
        }
        return $item;
    }

    /**
     * @Author: 广东七件事 zal
     * @Date: 2020-04-09
     * @Time: 11:55
     * @Note: 独立版
     * @return array
     */
    public static function getAdminMenus()
    {
        return [
            [
                'name' => '账户管理',
                'route' => '',
                'icon' => 'icon-setup',
                'children' => [
                    [
                        'name' => '我的账户',
                        'route' => 'admin/index/me',
                        'icon' => 'icon-person',
                    ],
                    /*[
                        'key' => 'account_list',// 超级管理员 显示
                        'name' => '账户列表',
                        'route' => 'admin/index/index',
                        'icon' => 'icon-liebiao',
                    ],*/
                    /*[
                        'key' => 'add_account',// 超级管理员 显示
                        'name' => '新增子账户',
                        'route' => 'admin/index/edit',
                        'icon' => 'icon-add1',
                    ],*/
//                    [
//                        'key' => 'register_audit',// 超级管理员 显示
//                        'name' => '注册审核',
//                        'route' => 'admin/index/register',
//                        'icon' => 'icon-liebiao',
//                    ],
                ]
            ],
            [
                'name' => '商城',
                'route' => '',
                'icon' => 'icon-setup',
                'children' => [
                    [
                        'key' => 'small_procedure',
                        'name' => '商城管理',
                        'route' => 'admin/mall/index',
                        'icon' => 'icon-shanghu',
                        'params' => [
                            '_layout' => 'admin',
                        ],
                        'action' => [
                            [
                                'name' => '添加编辑小程序',
                                'route' => 'admin/app/edit',
                            ],
                            [
                                'name' => '进入后台',
                                'route' => 'admin/app/entry',
                            ],
                            [
                                'name' => '删除商城',
                                'route' => 'admin/app/delete',
                            ],
                            [
                                'name' => '小程序回收站',
                                'route' => 'admin/mall/recycle',
                            ],
                            [
                                'name' => '设置回收站',
                                'route' => 'admin/app/set-recycle',
                            ],
                            [
                                'name' => '小程序禁用',
                                'route' => 'admin/app/disabled',
                            ],
                        ]
                    ],
                    [
                        'name' => '回收站',
                        'route' => 'admin/mall/recycle',
                        'icon' => 'icon-huishouzhan',
                    ],
                ]
            ],
            [
                'name' => '设置',
                'route' => '',
                'icon' => 'icon-setup',
                'children' => [
                    [
                        'key' => 'system_setting',// 超级管理员 显示
                        'name' => '系统设置',
                        'route' => 'admin/setting/index',
                        'icon' => 'icon-settings',
                    ],
                    [
                        'key' => 'attachment',
                        'name' => '账户上传管理',
                        'icon' => 'icon-shangchuan',
                        'route' => 'admin/setting/attachment',
                        'params' => [
                            '_layout' => 'admin',
                        ],
                    ],
                    [
                        'name' => '清理缓存',
                        'route' => 'admin/cache/clean',
                        'icon' => 'icon-qinglihuancun',
                        'params' => [
                            '_layout' => 'admin',
                        ],
                    ],
                    [
                        'key' => 'overrun',
                        'name' => '超限设置',
                        'route' => 'admin/setting/overrun',
                        'icon' => 'icon-qinglihuancun',
                        'params' => [
                            '_layout' => 'admin',
                        ],
                    ],
                    [
                        'key' => 'queue_service',
                        'name' => '队列服务',
                        'route' => 'admin/setting/queue-service',
                        'icon' => 'icon-qinglihuancun',
                        'params' => [
                            '_layout' => 'admin',
                        ],
                    ],
                    [
                        'name' => '小程序设置',
                        'route' => 'admin/setting/small-routine',
                        'icon' => 'icon-qinglihuancun',
                        'params' => [
                            '_layout' => 'admin',
                        ],
                    ],
                ]
            ],
        ];
    }
}
