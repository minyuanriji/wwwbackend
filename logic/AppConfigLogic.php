<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 系统配置处理
 * Author: zal
 * Date: 2020-04-13
 * Time: 14:36
 */

namespace app\logic;

use app\forms\admin\permission\branch\BaseBranch;
use app\forms\common\order\OrderCommon;
use app\forms\common\PickLinkForm;
use app\forms\mall\member\RegisterAgreeForm;
use app\forms\mall\option\RechargeSettingForm;
use app\forms\mall\shop\ColorForm;
use app\forms\mall\shop\CopyrightForm;
use app\forms\mall\shop\HomePageForm;
use app\forms\mall\shop\PosterForm;
use app\forms\mall\shop\TabbarForm;
use app\forms\mall\shop\UserCenterForm;
use app\forms\mall\sms\SmsForm;
use app\models\Admin;
use app\models\AliappConfig;
use app\models\ClerkUser;
use app\models\GoodsCats;
use app\models\HomePage;
use app\models\ImgMagic;
use app\models\Mall;
use app\models\MemberLevel;
use app\models\Option;
use app\models\User;
use app\models\UserInfo;
use app\services\Goods\PriceDisplayService;
use yii\helpers\ArrayHelper;

class AppConfigLogic
{
    /**
     * 底部导航设置
     * @return null
     */
    public static function getNavbar()
    {
        $option = OptionLogic::get(
            Option::NAME_NAVBAR,
            \Yii::$app->mall->id,
            Option::GROUP_APP,
            (new TabbarForm())->getDefault()
        );
        if (gettype($option['shadow']) === 'string') {
            $option['shadow'] = json_decode($option['shadow']);
        }
        return $option;
    }

    /**
     * 全局颜色设置
     * @return null
     */
    public static function getColor()
    {
        $option = OptionLogic::get(
            Option::NAME_COLOR,
            \Yii::$app->mall->id,
            Option::GROUP_APP,
            (new ColorForm())->getDefault()
        );
        return $option;
    }

    /**
     * 用户中心配置
     * @param $flag 1前台获取2后台获取
     * @return null
     * @throws \Exception
     */
    public static function getUserCenter($flag = 2)
    {
        $userCenterDefault = (new UserCenterForm())->getDefault();
        $option = OptionLogic::get(
            Option::NAME_USER_CENTER,
            \Yii::$app->mall->id,
            Option::GROUP_APP,
            $userCenterDefault
        );
        if (!isset($option['account_bar'])) $option['account_bar'] = $userCenterDefault['account_bar'];
        $option['account_bar']['integral']['navigate_enabled'] = false;
        foreach ($option['account_bar'] as $key => &$item) {
            switch ($key) {
                case 'balance':
                    $item['page_url'] = '/page/balance/balance';
                    break;
                case 'integral':
                    $item['page_url'] = '/plugins/score_mall/index/index';
                    break;
                case 'coupon':
                    $item['page_url'] = '/page/coupon/index/index';
                    break;
                case 'card':
                    $item['page_url'] = '/page/card/index/index';
                    break;
            }
        }
        unset($item);
        $arr = [];
        foreach ($option['order_bar'] as $k => $item) {
            $item['link_url'] = '/pages/order/list?status=' . $k;
            if ((int)$k + 1 === 5) {
                $item['link_url'] = '/pages/order/refund/list';
            }
            $item['open_type'] = PickLinkForm::OPEN_TYPE_NAVIGATE;
            $arr[] = $item;
        }
        $orderInfoCount = (new OrderCommon())->getOrderInfoCount();
        $arr[0]['text'] = $orderInfoCount[0] ?: '';
        $arr[1]['text'] = $orderInfoCount[1] ?: '';
        $arr[2]['text'] = $orderInfoCount[2] ?: '';
        $arr[3]['text'] = $orderInfoCount[3] ?: '';
        $arr[4]['text'] = $orderInfoCount[4] ?: '';
        $option['order_bar'] = $arr;

        //待使用的核销码
        if(isset($option['order_bar2'])){
            $offlineOrderInfoCount = (new OrderCommon())->getOfflineOrderInfoCount();
            $option['order_bar2'][0]['text'] = $offlineOrderInfoCount[0];
            $option['order_bar'][] = $option['order_bar2'][0];
        }

        if (!isset($option['member_bg_pic_url'])) {
            $option['member_bg_pic_url'] = $userCenterDefault['member_bg_pic_url'];
        }

        /** @var Admin $admin */
        $admin = \Yii::$app->mall->admin;
        $adminInfo = [];
        if($admin->admin_type != Admin::ADMIN_TYPE_SUPER){
            $adminInfo = \Yii::$app->mall->admin->adminInfo;
            if (!$adminInfo) {
                throw new \Exception('商城管理员不存在');
            }
        }
        /** @var BaseBranch $branchObject */
        $branchObject = \Yii::$app->branch;
        
        $permissions = $branchObject->childPermission($flag);

        if (count($option['account']) != 3) {
            $option['account'] = $userCenterDefault['account'];
            $res = OptionLogic::set(Option::NAME_USER_CENTER, $option, \Yii::$app->mall->id, Option::GROUP_APP);
        }

        $newArr = [];
        $name = 'score_mall';
        foreach ($option['account'] as $key => $item) {
            $item['is_show'] = 0;
            if ($key == 1) {
                $plugins = \Yii::$app->plugin->list;
                foreach ($plugins as $plugin) {
                    if ($plugin->name == $name) {
                        $item['is_show'] = 1;
                        $option['account_bar']['integral']['navigate_enabled'] = true;
                    }
                }

                // 判断是否为子账号商城，判断子账号商城是否有积分商城插件权限
                $admin = \Yii::$app->mall->admin;
                if ($item['is_show'] && $admin->admin_type != Admin::ADMIN_TYPE_SUPER) {
                    if (in_array($name, $permissions)) {
                        $item['is_show'] = 1;
                        $option['account_bar']['integral']['navigate_enabled'] = true;
                    } else {
                        $item['is_show'] = 0;
                        $option['account_bar']['integral']['navigate_enabled'] = false;
                        $option['account_bar']['integral']['page_url'] = '';
                    }
                }
            } else {
                $item['is_show'] = 1;
            }
            $newArr[] = $item;
        }
        $option['account'] = $newArr;
        //小程序端管理员管理入口权限
        if (!\Yii::$app->admin->isGuest) {
            $app_admin = true;

            if (\Yii::$app->admin->identity->admin_type != Admin::ADMIN_TYPE_SUPER) {
                if (\Yii::$app->admin->identity->admin_type == Admin::ADMIN_TYPE_OPERATE) {
                    if (empty(\Yii::$app->plugin->getInstalledPlugin('app_admin'))
                        || !in_array('app_admin', $permissions)) {
                        $app_admin = false;
                    }
                } else {
                    if (empty(\Yii::$app->plugin->getInstalledPlugin('app_admin'))
                        || !in_array('app_admin', $permissions)
                        || \Yii::$app->user->identity->identity->admin_type != Admin::ADMIN_TYPE_ADMIN) {
                        $app_admin = false;
                    }
                }
            }
            //小程序核销入口权限
            $clerk = true;
            $clerkUser = ClerkUser::findOne([
                'user_id' => \Yii::$app->user->id, 'mall_id' => \Yii::$app->mall->id, 'is_delete' => 0
            ]);
            if (\Yii::$app->admin->identity->admin_type != Admin::ADMIN_TYPE_SUPER && \Yii::$app->admin->identity->admin_type != Admin::ADMIN_TYPE_ADMIN) {
                if (empty(\Yii::$app->plugin->getInstalledPlugin('clerk'))
                    || !in_array('clerk', $permissions) || empty($clerkUser)) {
                    $clerk = false;
                }
            }
            // 加载会员图标,由于后台用户表与H5前端用户表分离，所以默认显示等级1的会员图标
            $level = MemberLevel::findOne([
                'mall_id' => \Yii::$app->mall->id,
                'level' => 1,
                'status' => 1,
                'is_delete' => 0
            ]);
            if ($level) {
                $option['member_pic_url'] = $level->pic_url;
            }

        } else {
            $app_admin = false;
            $clerk = false;
        }
        //剔除无权限入口
        $menu = [];
        foreach ($option['menus'] as $i => $v) {
            if ($v['open_type'] == 'app_admin' && !$app_admin) {
                continue;
            }
            if (strstr($v['link_url'], 'clerk') && !$clerk) {
                continue;
            }
            if ($v['open_type'] == 'contact' && \Yii::$app->appPlatform == User::PLATFORM_MP_TT) {
                continue;
            }
            if ($v['open_type'] == 'contact' && \Yii::$app->appPlatform == User::PLATFORM_MP_ALI) {
                $aliappConfig = AliappConfig::findOne([
                    'mall_id' => \Yii::$app->mall->id,
                ]);
                if ($aliappConfig) {
                    $v['tnt_inst_id'] = $aliappConfig->cs_tnt_inst_id;
                    $v['scene'] = $aliappConfig->cs_scene;
                }
            }
            $menu[] = $v;
        }
        $option['menus'] = $menu;

        //剔除无权限入口
        $user_tool_menus = [];
        if (!empty($option['user_tool_menus'])) {
            foreach ($option['user_tool_menus'] as $i => $v) {
                if ($v['open_type'] == 'app_admin' && !$app_admin) {
                    continue;
                }
                if (strstr($v['link_url'], 'clerk') && !$clerk) {
                    continue;
                }
                if ($v['open_type'] == 'contact' && \Yii::$app->appPlatform == User::PLATFORM_MP_TT) {
                    continue;
                }
                if ($v['open_type'] == 'contact' && \Yii::$app->appPlatform == User::PLATFORM_MP_ALI) {
                    $aliappConfig = AliappConfig::findOne([
                        'mall_id' => \Yii::$app->mall->id,
                    ]);
                    if ($aliappConfig) {
                        $v['tnt_inst_id'] = $aliappConfig->cs_tnt_inst_id;
                        $v['scene'] = $aliappConfig->cs_scene;
                    }
                }
                $user_tool_menus[] = $v;
            }
        }
        $option['user_tool_menus'] = $user_tool_menus;
        //钱包配置
        $option['recharge_setting'] = AppConfigLogic::getRechargeSetting();


        return $option;
    }

    /**
     * 商城版权设置
     * @return null
     */
    public static function getCoryRight()
    {
        $option = OptionLogic::get(
            Option::NAME_COPYRIGHT,
            \Yii::$app->mall->id,
            Option::GROUP_APP
        );
        $default = (new CopyrightForm())->getDefault();
        $option = self::check($option, $default);

        if (!isset($option['link'])) {
            $option['params'] = [];
            $option['link'] = [];
        }

        return $option;
    }

    /**
     * @param $mchId
     * @return null
     */
    public static function getSmsConfig($mchId = null)
    {
        if ($mchId === null || $mchId === '') {
            $isGuest = true;
            try {
                $isGuest = \Yii::$app->user->isGuest;
            } catch (\Exception $exception) {
            }
            if (!$isGuest) {
                $mchId = \Yii::$app->user->identity->mch_id;
            } else {
                $mchId = 0;
            }
        }
        $option = OptionLogic::get(
            Option::NAME_SMS,
            \Yii::$app->mall->id,
            Option::GROUP_ADMIN,
            null,
            $mchId
        );
        $default = (new SmsForm())->getDefault();
        $option = self::check($option, $default);

        return $option;
    }

    /**
     * 商城海报设置
     * @return null
     */
    public static function getPosterConfig()
    {
        $option = OptionLogic::get(
            Option::NAME_POSTER,
            \Yii::$app->mall->id,
            Option::GROUP_APP
        );
        $default = (new PosterForm())->getDefault();
        $option = self::check($option, $default);

        return $option;
    }

    /**
     * 已存储数据和默认数据对比，以默认数据字段为准
     * @param $list
     * @param $default
     * @return mixed
     */
    public static function check($list, $default)
    {
        foreach ($default as $key => $value) {
            if (!isset($list[$key])) {
                $list[$key] = $value;
                continue;
            }
            if (is_array($value)) {
                $list[$key] = self::check($list[$key], $value);
            }
        }
        return $list;
    }

    /**
     * 小程序首页配置(h5商城首页)
     * @return null
     */
    public static function getHomePageConfig()
    {
        $option = OptionLogic::get(
            Option::NAME_HOME_PAGE,
            \Yii::$app->mall->id,
            Option::GROUP_APP,
            (new HomePageForm())->getDefault()
        );

        // 排除分类 魔方已被删除的数据
        foreach ($option as $key => $item) {
            if ($item['key'] == 'cat' && $item['relation_id'] > 0) {
                $res = GoodsCats::find()->where([
                    'is_delete' => 0,
                    'id' => $item['relation_id'],
                    'mall_id' => \Yii::$app->mall->id,
                    'mch_id' => 0,
                ])->one();
                if (!$res) {
                    unset($option[$key]);
                }
            }
            if ($item['key'] == 'block' && $item['relation_id'] > 0) {
                $res = ImgMagic::find()->where([
                    'is_delete' => 0,
                    'id' => $item['relation_id'],
                    'mall_id' => \Yii::$app->mall->id,
                ])->one();
                if (!$res) {
                    unset($option[$key]);
                }
            }

            if ($item['key'] == 'coupon') {
                $option[$key]['coupon_url'] = $item['coupon_url'] ?: \Yii::$app->request->hostInfo . \Yii::$app->request->baseUrl . '/statics/img/mall/home_block/coupon-open.png';
                $option[$key]['coupon_not_url'] = $item['coupon_not_url'] ?: \Yii::$app->request->hostInfo . \Yii::$app->request->baseUrl . '/statics/img/mall/home_block/coupon-close.png';
                $option[$key]['discount_not_url'] = isset($item['discount_not_url']) && $item['discount_not_url'] ? $item['discount_not_url'] : \Yii::$app->request->hostInfo . \Yii::$app->request->baseUrl . '/statics/img/mall/home_block/discount-bg.png';
            }
        }
        $arr = ArrayHelper::toArray($option);

        return array_values($arr);
    }

    /**
     * 小程序充值
     * @return null
     */
    public static function getRechargeSetting()
    {
        $option = OptionLogic::get(
            Option::NAME_RECHARGE_SETTING,
            \Yii::$app->mall->id,
            Option::GROUP_APP
        );
        $default = (new RechargeSettingForm())->getDefault();
        $option = self::check($option, $default);
        return $option;
    }

    /**
     * 页面转发标题、图片设置
     * @return null
     */
    public static function getAppShareSetting()
    {
        $option = OptionLogic::get(
            Option::NAME_APP_SHARE_SETTING,
            \Yii::$app->mall->id,
            Option::GROUP_APP,
            []
        );
        return $option;
    }

    /**
     * @return array
     * 分类样式
     */
    public static function getAppCatStyle($mch_id = 0)
    {
        $option = OptionLogic::get(
            Option::NAME_CAT_STYLE_SETTING,
            \Yii::$app->mall->id,
            Option::GROUP_APP,
            [],
            $mch_id
        );

        $default = [
            'cat_style' => '3',
            'recommend_count' => '3',
            'cat_goods_count' => '1',
            'cat_goods_cols' => '1'
        ];
        $option = self::check($option, $default);

        return $option;
    }


    /**
     * @Author: 广东七件事 ganxiaohao
     * @Date: 2020-04-29
     * @Time: 17:29
     * @Note:获取页面标题配置
     * @return array
     */
    public static function getPageTitleConfig()
    {
        $option = OptionLogic::get(Option::NAME_PAGE_TITLE, \Yii::$app->mall->id, Option::GROUP_APP);

        $newOption = [];
        if ($option) {
            foreach ($option as $item) {
                $newOption[$item['name']] = $item;
            }
        }

        $default = PickLinkForm::getCommon()->getTitle();
        foreach ($default as $key => $item) {
            if ($item['value'] == '/pages/index/index') {
                unset($default[$key]);
            }
        }
        $default = array_values($default);
        foreach ($default as &$item) {
            if (isset($newOption[$item['name']])) {
                $item['new_name'] = $newOption[$item['name']]['new_name'];
            }
        }
        unset($item);

        return $default;
    }

    public static function getRecommendSetting()
    {
        $option = OptionLogic::get(
            Option::NAME_RECHARGE_SETTING,
            \Yii::$app->mall->id,
            Option::GROUP_APP,
            (new RechargeSettingForm())->getDefault()
        );
    }


    /**
     * @return array
     * 获取所有页面的默认配置（暂时只有授权页面）
     */
    public static function getDefaultPageList()
    {
        $picUrl = \Yii::$app->request->hostInfo . \Yii::$app->request->baseUrl . '/statics/img/app/mall';
        return [
            'auth' => [
                'pic_url' => $picUrl . '/auth-default.png',
                'hotspot' => [
                    'width' => '224',
                    'height' => '80',
                    'left' => '340',
                    'top' => '566',
                    'defaultX' => '340',
                    'defaultY' => '566',
                    'link' => '',
                    'open_type' => 'cancel'
                ],
                'hotspot_cancel' => [
                    'width' => '224',
                    'height' => '80',
                    'left' => '84',
                    'top' => '566',
                    'defaultX' => '84',
                    'defaultY' => '566',
                    'link' => '',
                    'open_type' => 'cancel'
                ]
            ]
        ];
    }

    /**
     * 保存海报各尺寸
     * @param array $default
     * @return array
     */
    public function saveEnd(array $default)
    {
        foreach ($default as $k => $i) {
            foreach ($i as $k1 => $i1) {
                if (in_array($k1, ['width', 'height', 'size', 'top', 'left'])) {
                    $default[$k][$k1] = (float)$default[$k][$k1] / 2;
                }
            }
        }
        return $default;
    }

    /**
     * 加载海报
     * @param $list
     * @param array $default
     * @return mixed
     */
    public function poster($list, $default = [])
    {
        $new_list = $this->check($list, $default);
        $check = ['width', 'height', 'size', 'top', 'left'];
        $checkArr = ['size', 'top', 'left', 'width', 'height', 'font', 'is_show', 'type'];
        // 将个别字段转为INT类型
        foreach ($new_list as $k => $posterItem) {
            foreach ($posterItem as $checkItemKey => $checkItem) {
                if (in_array($checkItemKey, $checkArr)) {
                    $new_list[$k][$checkItemKey] = (int)$posterItem[$checkItemKey];
                }
                if (in_array($checkItemKey, $check)) {
                    $new_list[$k][$checkItemKey] = $new_list[$k][$checkItemKey] * 2;
                }
            }
        }
        return $new_list;
    }


    /**
     * @Author: 广东七件事 ganxiaohao
     * @Date: 2020-05-07
     * @Time: 11:24
     * @Note:获取支付配置
     * @return bool|null
     */
    public static function getPaymentConfig($mall_id = "")
    {
        if (empty($mall_id)) {
            $mall_id = \Yii::$app->mall->id;
        }
        $payment = OptionLogic::get(Option::NAME_PAYMENT, $mall_id, Option::GROUP_APP);
        if (!$payment) {
            return false;
        }
        return $payment;
    }

    /**
     * @Author: 广东七件事 ganxiaohao
     * @Date: 2020-05-07
     * @Time: 11:24
     * @Note:获取退款原因配置
     * @return bool|null
     */
    public static function getRefundReasonConfig()
    {
        $reasonList = OptionLogic::get(Option::NAME_REFUND_REASON, \Yii::$app->mall->id, Option::GROUP_APP);
        if (empty($reasonList)) {
            return self::getDefaultRefundReasonList();
        }
        return $reasonList;
    }

    /**
     * 默认退货原因
     * @return array
     */
    private static function getDefaultRefundReasonList()
    {
        $data = [
            "商品信息描述不符",
            "退运费",
            "质量问题",
            "包装/商品破损/污渍",
            "其他问题",
        ];
        return $data;
    }

    /**
     * 获取手机相关设置数据
     * @return array
     * @throws \Exception
     */
    public static function getPhoneConfig()
    {
        $mall = new Mall();
        $settings = $mall->getMallSetting(["all_network_enable", "bind_phone_enable"]);
        $settings["all_network_enable"] = isset($settings["all_network_enable"]) ? $settings["all_network_enable"] : 1;
        $settings["bind_phone_enable"] = isset($settings["bind_phone_enable"]) ? $settings["bind_phone_enable"] : 1;
        return $settings;
    }

    /**
     * 获取商城配置
     * @param array $keys ["key1","key2"]
     * @return array
     * @throws \Exception
     */
    public static function getMallSettingConfig($keys)
    {
        $mall = new Mall();
        $returnData = [];
        if(!empty($keys)){
            $settings = $mall->getMallSetting($keys);
            foreach ($keys as $val){
                $returnData[$val] = isset($settings[$val]) ? $settings[$val] : "";
            }
        }
        return $returnData;
    }

    /**
     * 查找首页装修数据中的商品列表数据
     * @param $goodsData
     * @return HomePage|null
     */
    public static function findHomePageGoods($goodsData){
        $model = HomePage::findOne(['is_delete' => 0, 'mall_id' => \Yii::$app->mall->id]);
        if(!empty($model)){
            $returnData = [];
            $page_data = json_decode($model->page_data,true);
            if(!empty($page_data)){
                foreach ($page_data as $val){
                    $id = $val["id"];
                    if($id == "goods"){
                        $list = isset($val["data"]["list"]) ? $val["data"]["list"] : [];
                        if(!empty($list)){
                            foreach ($list as &$value){
                                if($value["id"] == $goodsData["id"]){
                                    $value["name"] = $goodsData["name"];
                                    $value["cover_pic"] = $goodsData["cover_pic"];
                                    $value["price"] = $goodsData["price"];
                                    $value["max_deduct_integral"] = $goodsData["max_deduct_integral"];

                                    $PriceDisplayService = new PriceDisplayService(\Yii::$app->mall->id);
                                    $price_display       = $PriceDisplayService->getGoodsPriceDisplay($goodsData["price_display"],false);
                                    $value["price_display"] = $price_display;
                                }
                            }
                            $val["data"]["list"] = $list;
                        }
                    }
                    $returnData[] = $val;
                }
                $model->page_data = json_encode($returnData,JSON_UNESCAPED_UNICODE);
                $model->save();
            }
        }
        return $model;
    }

    /**
     * 注册协议
     * @return null
     */
    public static function getRegisterAgree()
    {
        $option = OptionLogic::get(
            Option::NAME_REGISTER_AGREE,
            \Yii::$app->mall->id,
            Option::GROUP_APP
        );
        $default = (new RegisterAgreeForm())->getDefault();
        $option = self::check($option, $default);

        return $option;
    }
}
