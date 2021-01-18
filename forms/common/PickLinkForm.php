<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 跳转链接表单
 * Author: zal
 * Date: 2020-04-13
 * Time: 10:12
 */

namespace app\forms\common;

use app\core\ApiCode;
use app\models\BaseModel;


class PickLinkForm extends BaseModel
{
    const OPEN_TYPE_REDIRECT = 'redirect';
    const OPEN_TYPE_NAVIGATE = 'navigate';
    const OPEN_TYPE_XCX = 'app';
    const OPEN_TYPE_CONTACT = 'contact';
    const OPEN_TYPE_CLEAR_CACHE = 'clear_cache';
    const OPEN_TYPE_CALL = 'call';

    // 忽略的场景
    const IGNORE_TITLE = 'title'; // 页面标题
    const IGNORE_NAVIGATE = 'navigate'; // 导航底栏

    public $ignore;

    /**
     * 小程序菜单跳转链接
     * @param $links
     * @return mixed|string
     */
    public function getList($links)
    {
//        $links = $this->links();

        $list = [];
        $id = 1;
        foreach ($links as $k => $item) {
            $item['id'] = $id++;
            $list[] = $item;
        }

        $newList = [];
        foreach ($list as $item) {
            if ($this->ignore && isset($item['ignore']) && in_array($this->ignore, $item['ignore'])) {
                continue;
            }
            if (isset($item['type']) && $item['type'] === 'base') {
                $newList['base'][] = $item;
            } elseif (isset($item['type']) && $item['type'] === 'marketing') {
                $newList['marketing'][] = $item;
            } elseif (isset($item['type']) && $item['type'] === 'order') {
                $newList['order'][] = $item;
            } elseif (isset($item['type']) && $item['type'] === 'diy') {
                $newList['diy'][] = $item;
            } else {
                $newList['plugin'][] = $item;
            }
        }

        return $newList;
    }

    /**
     * 导航链接
     * @return array
     * [
     * type: 所属的标签base--基础|marketing--营销|plugin--插件|order--订单|diy--diy
     * name: 链接名称
     * open_type: 链接操作方式 OPEN_TYPE_REDIRECT--跳转|OPEN_TYPE_NAVIGATE--重定向|OPEN_TYPE_XCX--跳转小程序|OPEN_TYPE_CALL--客服
     *            |OPEN_TYPE_CLEAR_CACHE--清除缓存|OPEN_TYPE_CALL--拨号
     * icon: 链接图标
     * value: 链接的路径
     * params: 链接上的参数
     * key: 链接的权限
     * ignore: 链接忽略场景 IGNORE_TITLE--页面标题|IGNORE_NAVIGATE--导航底栏
     * ]
     */
    private function links()
    {
        $iconUrlPrefix = \Yii::$app->request->hostInfo . \Yii::$app->request->baseUrl .
            '/statics/img/mall/pick-link/';





        $list = [
            [
                'type' => 'base',
                'name' => '商城首页',
                'open_type' => '',
                'icon' => $iconUrlPrefix . 'icon-index.png',
                'value' => '/pages/index/index',

            ],
            [
                'type' => 'marketing',
                'name' => '推广中心',
                'open_type' => '',
                'icon' => $iconUrlPrefix . 'icon-share-center.png',
                'value' => '/plugins/extensions/index',

                'key' => 'extensions',
            ],
            [
                'type' => 'marketing',
                'name' => '我的优惠券',
                'open_type' => '',
                'icon' => $iconUrlPrefix . 'icon-user-coupons.png',
                'value' => '/pages/user/coupon/coupon',

                'key' => 'coupon',
            ],
            [
                'type' => 'marketing',
                'name' => '领券中心',
                'open_type' => '',
                'icon' => $iconUrlPrefix . 'icon-coupons.png',
                'value' => '/pages/coupon/list',

                'key' => 'coupon',
            ],
            [
                'type' => 'base',
                'name' => '我的收藏',
                'open_type' => '',
                'icon' => $iconUrlPrefix . 'icon-favorite.png',
                'value' => '/pages/user/collect/list',
            ],
            [
                'type' => 'base',
                'name' => '我的设置',
                'open_type' => '',
                'icon' => $iconUrlPrefix . 'icon-setting.png',
                'value' => '/pages/user/setting',

            ],
            [
                'type' => 'marketing',
                'name' => '积分明细',
                'open_type' => '',
                'icon' => $iconUrlPrefix . 'icon-integral.png',
                'value' => '/pages/user/score/list',
            ],
            [
                'type' => 'base',
                'name' => '联系我们',
                'open_type' => self::OPEN_TYPE_CALL,
                'icon' => $iconUrlPrefix . 'icon-contact.png',
                'value' => 'tel',
                'params' => [
                    [
                        'key' => 'tel',
                        'value' => '',
                        'desc' => '请填写联系电话',
                        'is_required' => true,
                        'data_type' => 'number'
                    ]
                ]
            ],
            [
                'type' => 'base',
                'name' => '文章列表',
                'open_type' => '',
                'icon' => $iconUrlPrefix . 'icon-article.png',
                'value' => '/pages/article/list',
            ],
            [
                'type' => 'base',
                'name' => '文章详情',
                'open_type' => '',
                'icon' => $iconUrlPrefix . 'icon-article-detail.png',
                'value' => '/pages/article/detail',
                'params' => [
                    [
                        'key' => 'id',
                        'value' => '',
                        'desc' => '请填写文章ID',
                        'is_required' => true,
                        'data_type' => 'number',
                        'page_url' => 'mall/article/detail',
                        'pic_url' => $iconUrlPrefix . 'example_image/article-id.png',
                        'page_url_text' => '内容管理->文章列表'
                    ]
                ]
            ],
            [
                'type' => 'base',
                'name' => '收货地址',
                'open_type' => '',
                'icon' => $iconUrlPrefix . 'icon-address.png',
                'value' => '/pages/user/address/list',
            ],
            [
                'type' => 'order',
                'name' => '我的订单',
                'open_type' => '',
                'icon' => $iconUrlPrefix . 'icon-order.png',
                'value' => '/pages/order/list',
                'params' => [
                    [
                        'key' => 'status',
                        'value' => '',
                        'desc' => "status 为订单列表状态, 为空则跳转为全部订单",
                        'is_required' => false,
                        'data_type' => 'number'
                    ]
                ],
                'ignore' => [PickLinkForm::IGNORE_TITLE],
            ],
            [
                'type' => 'order',
                'name' => '全部订单',
                'open_type' => '',
                'icon' => $iconUrlPrefix . 'icon-order-all.png',
                'value' => '/pages/order/list',
                'ignore' => [PickLinkForm::IGNORE_TITLE],
            ],
            [
                'type' => 'order',
                'name' => '待付款',
                'open_type' => '',
                'icon' => $iconUrlPrefix . 'icon-order-0.png',
                'value' => '/pages/order/list?status=0',
                'ignore' => [PickLinkForm::IGNORE_TITLE],
            ],
            [
                'type' => 'order',
                'name' => '待发货',
                'open_type' => '',
                'icon' => $iconUrlPrefix . 'icon-order-1.png',
                'value' => '/pages/order/list?status=1',
                'ignore' => [PickLinkForm::IGNORE_TITLE],
            ],
            [
                'type' => 'order',
                'name' => '待收货',
                'open_type' => '',
                'icon' => $iconUrlPrefix . 'icon-order-2.png',
                'value' => '/pages/order/list?status=2',
                'ignore' => [PickLinkForm::IGNORE_TITLE],
            ],
            [
                'type' => 'order',
                'name' => '待评价',
                'open_type' => '',
                'icon' => $iconUrlPrefix . 'icon-order-3.png',
                'value' => '/pages/order/list?status=3',
                'ignore' => [PickLinkForm::IGNORE_TITLE],
            ],
            [
                'type' => 'order',
                'name' => '售后',
                'open_type' => '',
                'icon' => $iconUrlPrefix . 'icon-order-4.png',
                'value' => '/pages/order/refund/list',
                'ignore' => [PickLinkForm::IGNORE_TITLE],
            ],
            [
                'type' => 'base',
                'name' => '清除缓存',
                'open_type' => self::OPEN_TYPE_CLEAR_CACHE,
                'icon' => $iconUrlPrefix . 'icon-clear-cache.png',
                'value' => self::OPEN_TYPE_CLEAR_CACHE,
                'ignore' => [PickLinkForm::IGNORE_TITLE],
            ],
            [
                'type' => 'base',
                'name' => '购物车',
                'open_type' => '',
                'icon' => $iconUrlPrefix . 'icon-cart.png',
                'value' => '/pages/cart/index',
            ],
            [
                'type' => 'base',
                'name' => '分类',
                'open_type' => '',
                'icon' => $iconUrlPrefix . 'icon-cats.png',
                'value' => '/pages/cat/cat',
            ],
            [
                'type' => 'base',
                'name' => '用户中心',
                'open_type' => '',
                'icon' => $iconUrlPrefix . 'icon-user-center.png',
                'value' => '/pages/user/index',
            ],
            [
                'type' => 'base',
                'name' => '商品列表',
                'open_type' => '',
                'icon' => $iconUrlPrefix . 'icon-goods.png',
                'value' => '/pages/goods/list',
                'params' => [
                    [
                        'key' => 'cat_id',
                        'value' => "",
                        'desc' => 'cat_id 请填写在商品分类中相关分类的ID',
                        'is_required' => false,
                        'data_type' => 'number',
                        'page_url' => 'mall/cat/index',
                        'pic_url' => $iconUrlPrefix . 'example_image/cat-id.png',
                        'page_url_text' => '商品管理->分类'
                    ]
                ]
            ],
            [
                'type' => 'base',
                'name' => '商品详情',
                'open_type' => '',
                'icon' => $iconUrlPrefix . 'icon-goods-detail.png',
                'value' => '/pages/goods/detail',
                'params' => [
                    [
                        'key' => 'proId',
                        'value' => '',
                        'desc' => 'id请填写在商品列表中相关商品的ID',
                        'is_required' => true,
                        'data_type' => 'number',
                        'page_url' => 'mall/goods/index',
                        'pic_url' => $iconUrlPrefix . 'example_image/goods-id.png',
                        'page_url_text' => '商品管理->商品列表'
                    ]
                ],
                'ignore' => [PickLinkForm::IGNORE_TITLE, PickLinkForm::IGNORE_NAVIGATE],
            ],
            [
                'type' => 'base',
                'name' => '专题列表',
                'open_type' => '',
                'icon' => $iconUrlPrefix . 'icon-topic.png',
                'value' => '/pages/topic/list',
                'params' => [
                    [
                        'key' => 'type',
                        'value' => '',
                        'desc' => 'type请填写在专题分类中的ID',
                        'is_required' => false,
                        'data_type' => 'number',
                        'page_url' => 'mall/topic-type/index',
                        'pic_url' => $iconUrlPrefix . 'example_image/topic-cat-id.png',
                        'page_url_text' => '内容管理->专题分类'
                    ]
                ],
                'key' => 'topic',
            ],
            [
                'type' => 'base',
                'name' => '专题详情',
                'open_type' => '',
                'icon' => $iconUrlPrefix . 'icon-topic-detail.png',
                'value' => '/pages/topic/detail',
                'params' => [
                    [
                        'key' => 'id',
                        'value' => '',
                        'desc' => 'id 请填写在专题列表中相关专题的ID',
                        'is_required' => true,
                        'data_type' => 'number',
                        'page_url' => 'mall/topic/index',
                        'pic_url' => $iconUrlPrefix . 'example_image/topic-id.png',
                        'page_url_text' => '内容管理->专题'
                    ]
                ],
                'key' => 'topic',
                'ignore' => [PickLinkForm::IGNORE_NAVIGATE],
            ],
            [
                'type' => 'base',
                'name' => '跳转小程序',
                'open_type' => self::OPEN_TYPE_XCX,
                'icon' => $iconUrlPrefix . 'icon-mini.png',
                'value' => self::OPEN_TYPE_XCX,
//                'page_url' => 'plugin/wxapp/com-upload',
//                'page_url_text' => '小程序发布->可跳转小程序设置',
                'remark' => '每次设置跳转,都需到小程序发布,重新添加跳转小程序appId,并重新发布。',
                'params' => [
                    [
                        'key' => 'app_id',
                        'value' => '',
                        'desc' => '[微信]要打开的小程序AppId',
                        'is_required' => false
                    ],
                    [
                        'key' => 'path',
                        'value' => '',
                        'desc' => '[微信]打开的页面路径，如pages/index/index，开头请勿加“/”',
                        'is_required' => false
                    ],
                    [
                        'key' => 'ali_app_id',
                        'value' => '',
                        'desc' => '[支付宝]要打开的小程序AppId',
                        'is_required' => false
                    ],
                    [
                        'key' => 'ali_path',
                        'value' => '',
                        'desc' => '[支付宝]打开的页面路径，如pages/index/index，开头请勿加“/”',
                        'is_required' => false
                    ],
                    [
                        'key' => 'tt_app_id',
                        'value' => '',
                        'desc' => '[抖音头条]要打开的小程序AppId',
                        'is_required' => false
                    ],
                    [
                        'key' => 'tt_path',
                        'value' => '',
                        'desc' => '[抖音头条]打开的页面路径，如pages/index/index，开头请勿加“/”',
                        'is_required' => false
                    ],

                    [
                        'key' => 'bd_app_key',
                        'value' => '',
                        'desc' => '[百度]要打开的小程序AppKey',
                        'is_required' => false
                    ],
                    [
                        'key' => 'bd_path',
                        'value' => '',
                        'desc' => '[百度]打开的页面路径，如pages/index/index，开头请勿加“/”',
                        'is_required' => false
                    ],
                ],
                'ignore' => [PickLinkForm::IGNORE_TITLE],
            ],
            [
                'type' => 'base',
                'name' => '网页链接',
                'open_type' => 'web',
                'icon' => $iconUrlPrefix . 'icon-web-link.png',
                'value' => '/pages/web/web',
                'params' => [
                    [
                        'key' => 'url',
                        'value' => '',
                        'desc' => '打开的网页链接（注：域名必须已在微信官方小程序平台设置业务域名）',
                        'is_required' => true
                    ]
                ],
                'ignore' => [PickLinkForm::IGNORE_TITLE],
            ],
            [
                'type' => 'base',
                'name' => '门店列表',
                'open_type' => '',
                'icon' => $iconUrlPrefix . 'icon-store.png',
                'value' => '/pages/store/store',
            ],
            [
                'type' => 'marketing',
                'name' => '充值中心',
                'open_type' => '',
                'icon' => $iconUrlPrefix . 'icon-recharge.png',
                'value' => '/pages/balance/recharge',
            ],
            [
                'type' => 'marketing',
                'name' => '余额记录',
                'open_type' => '',
                'icon' => $iconUrlPrefix . 'icon-recharge.png',
                'value' => '/pages/balance/balance',
            ],
            [
                'type' => 'base',
                'name' => '搜索页',
                'open_type' => '',
                'icon' => $iconUrlPrefix . 'icon-search.png',
                'value' => '/pages/search/search',
            ],
//            [
//                'type' => 'base',
//                'name' => '客服',
//                'open_type' => self::OPEN_TYPE_CALL,
//                'icon' => $iconUrlPrefix . 'icon-service.png',
//                'value' => self::OPEN_TYPE_CALL,
//                'ignore' => [self::IGNORE_TITLE]
//            ],
//            [
//                'type' => 'base',
//                'name' => '视频专区',
//                'open_type' => '',
//                'icon' => $iconUrlPrefix . 'icon-search.png',
//                'value' => '/pages/video/video',
//                'key' => 'video',
//            ],
            [
                'type' => 'base',
                'name' => '我的足迹',
                'open_type' => '',
                'icon' => $iconUrlPrefix . 'icon-foot.png',
                'value' => '/pages/user/footmark/list',
                'ignore' => [PickLinkForm::IGNORE_NAVIGATE],
            ],
//            [
//                'type' => 'base',
//                'name' => '账单总结',
//                'open_type' => '',
//                'icon' => $iconUrlPrefix . 'icon-summary.png',
//                'value' => '/pages/foot/summary/summary',
//                'ignore' => [PickLinkForm::IGNORE_NAVIGATE],
//            ],
            [
                'type' => 'base',
                'name' => '推广中心',
                'open_type' => '',
                'icon' => $iconUrlPrefix . 'icon-summary.png',
                'value' => '/plugins/extensions/index',
                'ignore' => [PickLinkForm::IGNORE_NAVIGATE],
            ],
//            [
//                'type' => 'base',
//                'name' => '推广二维码',
//                'open_type' => '',
//                'icon' => $iconUrlPrefix . 'icon-summary.png',
//                'value' => '/pages/extensions/poster',
//                'ignore' => [PickLinkForm::IGNORE_NAVIGATE],
//            ],
            [
                'type' => 'base',
                'name' => '推广二维码',
                'open_type' => '',
                'icon' => $iconUrlPrefix . 'icon-summary.png',
                'value' => '/plugins/extensions/poster',
                'ignore' => [PickLinkForm::IGNORE_NAVIGATE],
            ]
        ];

        $newList = [];
        try {
            foreach ($list as $item) {
                if (\Yii::$app->role->checkLink($item)) {
                    $newList[] = $item;
                }
            }

            $plugins = \Yii::$app->role->getPluginList();
            foreach ($plugins as $plugin) {
                if (method_exists($plugin, 'getPickLink')) {
                    $newList = array_merge($newList, $plugin->getPickLink());
                }
            }
        } catch (\Exception $exception) {
            $newList = $list;
            $plugins = \Yii::$app->plugin->list;
            foreach ($plugins as $plugin) {
                $PluginClass = 'app\\plugins\\' . $plugin->name . '\\Plugin';
                /** @var Plugin $pluginObject */
                if (!class_exists($PluginClass)) {
                    continue;
                }
                $object = new $PluginClass();
                if (method_exists($object, 'getPickLink')) {
                    $newList = array_merge($newList, $object->getPickLink());
                }
            }
        }

        foreach ($newList as &$item) {
            if (!$item['open_type']) {
                $item['open_type'] = 'navigate';
            }
        }
        unset($item);

        return $newList;
    }

    /**
     * @return array
     * 小程序页面链接（去掉一部分分页面链接）
     */
    public function appPage()
    {
        $list = $this->links();
        $baseUrl=\Yii::$app->request->hostInfo.'/h5/?mall_id='.\Yii::$app->mall->id.'#';
        foreach ($list as $index => $item) {
            if (!($item['open_type'] == '' || $item['open_type'] == 'navigate' || $item['open_type'] == 'redirect')) {
                unset($list[$index]);
            }
        }
        $list = array_values($list);
        $list = $this->getList($list);
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '请求成功',
            'data' => [
                'list' => $list,
                'base_url'=>$baseUrl
            ]
        ];
    }

    /**
     * @return array
     * 跳转链接
     */
    public function getLink()
    {
        $list = $this->links();
        $list = $this->getList($list);


        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '请求成功',
            'data' => [
                'list' => $list
            ]
        ];
    }


    /**
     * @return array
     * 小程序端页面标题
     */
    public function getTitle()
    {
        $res = $this->links();

        $newList = [];
        foreach ($res as $item) {
            // 删除不需要在标题中显示的内容
            if (isset($item['ignore']) && in_array('title', $item['ignore'])) {
                continue;
            }
            if ($item['value']) {
                $newList[] = [
                    'name' => $item['name'],
                    'value' => $item['value'],
                    'new_name' => $item['name'],
                ];
            }
        }

        return $newList;
    }

    public static function getCommon()
    {
        return new self();
    }
}
