<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 基础model
 * Author: zal
 * Date: 2020-04-08
 * Time: 15:12
 */

namespace app\models;

use app\core\payment\Payment;
use app\forms\common\version\Compatible;
use Yii;

/**
 * This is the model class for table "{{%mall}}".
 *
 * @property integer $id
 * @property integer $created_at
 * @property integer $updated_at
 * @property integer $deleted_at
 * @property integer $expired_at
 * @property string $name
 * @property integer $admin_id
 * @property integer $is_recycle
 * @property integer $is_disable
 * @property integer $is_delete
 * @property Admin $admin
 * @property MallSetting[] $option
 */
class Mall extends BaseActiveRecord
{
    public $options;

    /** @var int 是否回收 0否 */
    const IS_RECYLE_NO = 0;
    /** @var int 是否回收 1是 */
    const IS_RECYLE_YES = 1;

    /** @var int 是否禁用 0否 */
    const IS_DISABLE_ON = 0;
    /** @var int 是否回收 1是 */
    const IS_DISABLE_OFF = 1;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%mall}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['created_at', 'updated_at', 'deleted_at', 'expired_at'], 'safe'],
            [['admin_id', 'is_recycle', 'is_delete', 'is_disable'], 'integer'],
            [['name'], 'string', 'max' => 64],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'deleted_at' => 'Deleted Time',
            'expired_at' => 'Expired Time',
            'name' => '商城名称',
            'admin_id' => '用户 ID',
            'is_recycle' => '商城回收状态',
            'is_disable' => '商城禁用状态',
            'is_delete' => 'Is Delete',
        ];
    }

    public function getAdmin()
    {
        return $this->hasOne(Admin::className(), ['id' => 'admin_id']);
    }

    public function getOption()
    {
        return $this->hasMany(MallSetting::className(), ['mall_id' => 'id']);
    }


    /**
     * @Author: 广东七件事 ganxiaohao
     * @Date: 2020-04-27
     * @Time: 16:51
     * @Note:获取商城单个配置
     * @param $column
     * @return mixed|null
     * @throws \Exception
     */
    public function getMallSettingOne($column)
    {
        $settings = $this->getMallSetting([$column]);
        if (isset($settings[$column])) {
            return $settings[$column];
        }
        return null;
    }

    /**
     * @Author: 广东七件事 ganxiaohao
     * @Date: 2020-04-16
     * @Time: 16:29
     * @Note:获取商城配置
     * @param array $columns
     * @return array
     * @throws \Exception
     */
    public function getMallSetting(array $columns = [])
    {
        $detail = Yii::$app->mall->toArray();
        $detail['option'] = Yii::$app->mall->option;
        $defaultList = $this->getDefault();
        $waitAddData = [];
        // 查找出列表中 没有的默认参数
        foreach ($defaultList as $dKey => $dItem) {
            $sign = false;
            foreach ($detail['option'] as $item) {
                if ($dKey == $item['key']) {
                    $sign = true;
                }
            }
            if (!$sign) {
                $waitAddData[$dKey] = $dItem;
            }
        }
        foreach ($waitAddData as $key => $item) {
            $detail['option'][] = [
                'key' => $key,
                'value' => $item
            ];
        }
        $newOption = [];
        foreach ($detail['option'] as $k => $item) {

            if (in_array($item['key'], ['send_type', 'business_time_type_week', 'business_time_type_day','integral_price_display','price_display'])) {
                $value = is_array($item['value']) ? $item['value'] : json_decode($item['value'], true);
                $newOption[$item['key']] = $value;
            } else {

                if ($item['key'] == 'web_service_url') {
                    $newOption[$item['key']] = urldecode($item['value']);
                } else {
                    $newOption[$item['key']] = $item['value'];
                    if ($item['key'] == 'web_url') {
                        $newOption[$item['key']] = urldecode($item['value']);
                    }
                }
            }
        }

        // 添加商城配置默认值
        $defaultArr = $this->getDefault();
        foreach ($defaultArr as $k => $item) {
            if (!isset($newOption[$k])) {
                $newOption[$k] = $item;
            }
        }
        // 返回指定字段配置
        if ($columns) {
            $newData = [];
            foreach ($columns as $column) {
                if (!isset($newOption[$column])) {
                    throw new \Exception('字段' . $column . '不存在');
                }
                $newData[$column] = $newOption[$column];
            }
            return $newData;
        }
        $detail['setting'] = $newOption;
        return $detail;
    }

    /**
     * TODO 商城配置默认值
     * @return array
     */
    public function getDefault()
    {
        $host = PHP_SAPI != 'cli' ? \Yii::$app->request->hostInfo . \Yii::$app->request->baseUrl . "/" : '';
        return [
            'app_share_title' => '', //分享标题
            'app_share_desc' => '', //分享标题
            'app_share_pic' => '', //分享图片
            'contact_tel' => '',// 联系电话
            'cancel_at' => 0, // 未支付订单超时时间（分钟）
            'delivery_time' => 15, // 收货时间（天）
            'after_sale_time' => 0, // 售后时间（天）
            'send_type' => [
                'express', 'offline'
            ],// 发货方式 express--快递 offline--自提 city--同城
            'express_aliapy_code' => '', //支付宝code
            'kdniao_mch_id' => '',// 快递鸟商户ID
            'kdniao_api_key' => '',// 快递鸟API KEY
            'score' => '0',// 会员积分抵扣比例
            'score_rule' => '',// 会员积分使用规格
            /**
             * 商品面议联系方式
             * contact 客服
             * contact_tel 联系电话
             * contact_web 外链客服
             */
            'good_negotiable' => [
                'contact',
            ],
            'mobile_verify' => '1', // 商城手机号是否验证 0.关闭 1.开启
            'is_customer_services' => '0', // 是否开启在线客服 0.关闭 1.开启
            'customer_services_pic' => $host . 'statics/img/mall/customer_services_pic.png',// 在线客服图标
            'is_dial' => '0',// 是否开启一键拨号 0.关闭 1.开启
            'dial_pic' => $host . 'statics/img/mall/dial_pic.png',// 一键拨号图标
            'is_web_service' => '0',// 客服外链开关
            'web_service_url' => '', // 客服外链
            'web_service_pic' => $host . 'statics/img/mall/web_service_pic.png', // 客服外链图标
            'is_show_sale_out' => '1', // 是否显示售罄图标
            'is_use_sale_out' => '1', //是否使用默认的售罄图标
            'sale_out_pic' => '', //售罄图标
            'sale_out_other_pic' => '', //4:3售罄图片
            /**
             * 快捷导航样式
             * 1.样式1（点击收起）
             * 2.样式2（全部展示）
             */
            'is_quick_navigation' => '0',
            'quick_navigation_style' => '1',//1.样式1（点击收起）2.样式2（全部展示）
            'quick_navigation_opened_pic' => $host . 'statics/img/mall/quick_navigation_opened_pic.png',// 快捷导航展开图标
            'quick_navigation_closed_pic' => $host . 'statics/img/mall/quick_navigation_closed_pic.png',// 快捷导航收起图标
            'is_common_user_member_price' => '1',// 普通用户会员价显示开关 0.关闭 1.开启
            'is_member_user_member_price' => '1',// 会员用户会员价显示开关 0.关闭 1.开启
            'is_distribution_price' => '1',// 分销价显示开关 0.关闭 1.开启
            'is_purchase_frame' => '1',// 首页购买记录框 0.关闭 1.开启
            'purchase_num' => '0', //轮播订单数
            'is_comment' => '1', // 商城评价开关 0.关闭 1.开启
            'is_sales' => '1',// 商城商品销量开关 0.关闭 1.开启
            // 'is_recommend' => '1',// TODO 即将废弃 推荐商品状态 0.关闭 1.开启
            'is_mobile_auth' => '0',// 首页授权手机号 0.关闭 1.开启
            'is_official_account' => '0', // 关联公众号组件 0.关闭 1.开启
            'is_manual_mobile_auth' => '1', // 手动授权手机号 0.关闭 1.开启
            'is_icon_members_grade' => '0', //会员等级标识 0关闭 1.开启
            'is_quick_map' => '0', // 一键导航是否开启 0.关闭 1.开启
            'quick_map_pic' => $host . 'statics/img/mall/quick_map_pic.png', // 一键导航图标
            'quick_map_address' => '', // 商家地址
            'latitude' => '', //纬度
            'longitude' => '', // 经度
            'is_quick_home' => '0',//返回首页开关
            'quick_home_pic' => $host . 'statics/img/mall/quick_home_pic.png',// 返回首页图标
            'is_show_cart' => '1',// 购物车显示开关
            'is_show_sales_num' => '1', //已售量（商品列表） 0.关闭  1.开启
            'is_show_goods_name' => '1', //商品名称
            'is_underline_price' => '1', //划线价
            'is_express' => '1', //快递
            'is_not_share_show' => '1', //非分销商分销中心显示
            'is_show_cart_hover' => '0',//购物车悬浮按钮
            'is_show_scroll_top' => '0',   //回到顶部悬浮按钮
            'all_network_enable'=>'0',//全网通
            'bind_phone_enable'=>'0',//绑定手机号
            'close_auth_bind'=>'0',//关闭小程序手机授权绑定
            'logo' => '', //手机端商城管理店铺设置页面，logo可自定义图片上传
            //支付方式
            'payment_type' => [
                Payment::PAY_TYPE_WECHAT,
            ],
        ];
    }
}
