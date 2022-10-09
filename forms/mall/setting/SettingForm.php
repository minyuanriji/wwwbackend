<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * Created by PhpStorm
 * Author: ganxiaohao
 * Date: 2020-04-16
 * Time: 16:39
 */

namespace app\forms\mall\setting;


use app\core\ApiCode;
use app\models\BaseModel;
use app\models\MallSetting;
use yii\helpers\Html;

class SettingForm extends BaseModel
{
    public $name; //商城名称
    public $contact_tel;//联系方式
    public $cancel_at;
    public $delivery_time; //自动确认收货时间
    public $after_sale_time; //售后时间
    public $send_type;// 发货方式
    public $express_aliapy_code; //阿里
    public $kdniao_mch_id; //快递鸟商户ID
    public $kdniao_api_key;//快递api 密钥
    public $score;//积分
    public $score_rule; //积分规则
    public $mobile_verify;//验证手机号码格式
    public $is_customer_services;//在线客服
    public $customer_services_pic;//在线客服图标
    public $is_web_service;//是否启用客服外链
    public $web_service_url;//客服外链地址
    public $web_url;//网站地址
    public $web_service_pic;//外链客服图标
    public $is_quick_navigation;//是否启用悬浮导航
    public $quick_navigation_style;//悬浮导航风格
    public $quick_navigation_opened_pic;//展开图标
    public $quick_navigation_closed_pic;//收回图标
    public $is_show_sale_out;//显示售罄
    public $is_use_sale_out;//是否启用显示售罄
    public $sale_out_pic;//售罄图标
    public $sale_out_other_pic;
    public $is_common_user_member_price;//是否显示VIP会员会员价
    public $is_member_user_member_price;//是否显示会员用户会员价
    public $is_comment;//显示评论
    public $is_sales;//显示销量
    public $is_icon_members_grade;//显示会员等级
    public $is_quick_map;//快捷导航地图
    public $quick_map_pic;//快捷导航地图图标
    public $quick_map_address;//快捷地图地址
    public $longitude;//经度
    public $latitude;//纬度
    public $is_quick_home;//回到主页按钮
    public $quick_home_pic;//回到主页图标
    public $logo;//商城logo
    public $is_close;
    public $business_time_type;
    public $business_time_custom_type;
    public $business_time_type_day;
    public $business_time_type_week;
    public $auto_business;
    public $auto_business_time;

    public $all_network_enable; //是否开启全网通
    public $bind_phone_enable; //是否开启手机绑定

    public $is_show_cart;    //显示购物车
    public $is_show_sales_num;//显示销量
    public $is_show_goods_name; //是否显示商品名称
    public $is_underline_price;//显示画线价
    public $is_express; //商品显示运费
    public $is_show_cart_hover;   //购物车悬浮按钮
    public $is_show_scroll_top;    //回到顶部悬浮按钮
    public $over_time; //订单超时未支付关闭的时间

    public $app_share_title;    //分享标题
    public $app_share_pic; //分享图片
    public $app_share_desc; //分享描述
    public $close_auth_bind; //分享描述

    public $is_town;



    public function rules()
    {
        return [
            [['name'], 'trim',],
            [['contact_tel', 'kdniao_mch_id', 'kdniao_api_key', 'score_rule',
                'customer_services_pic',
                  'web_service_url','web_url','web_service_pic', 'quick_navigation_closed_pic',
                'quick_navigation_opened_pic', 'quick_map_pic', 'quick_map_address', 'longitude', 'latitude',
                'quick_home_pic', 'logo',
                'sale_out_pic', 'sale_out_other_pic','app_share_title','app_share_pic','app_share_desc','express_aliapy_code'], 'string'],

            [['delivery_time', 'after_sale_time', 'score',
                'mobile_verify', 'is_customer_services', 'quick_navigation_style',
                'is_common_user_member_price', 'is_member_user_member_price',
                'is_comment', 'is_sales', 'is_icon_members_grade',
                'is_quick_map', 'is_web_service', 'is_quick_navigation',
                'is_quick_home', 'is_close', 'business_time_type',
                'business_time_custom_type', 'auto_business','is_show_cart', 'is_show_sales_num', 'is_show_goods_name', 'is_underline_price',
                'is_express', 'is_show_cart_hover', 'is_show_scroll_top',
                'is_show_sale_out', 'is_use_sale_out', 'over_time','all_network_enable','bind_phone_enable','is_town','close_auth_bind'], 'integer'],
            [['name'], 'required',],
            [['sale_out_pic', 'sale_out_other_pic','express_aliapy_code'], 'default', 'value' => ''],
            [[ 'send_type', 'business_time_type_day', 'business_time_type_week',
                'auto_business_time'], 'safe'],
        ];
    }

    public function save()
    {
        $this->name = Html::encode($this->name);
        if (!$this->validate()) {
            return $this->responseErrorInfo($this);
        }

        $transaction = \Yii::$app->db->beginTransaction();
        try {
            foreach ($this->attributes as $k => $item) {
                $arr = ['name', 'latitude_longitude'];
                if (in_array($k, $arr)) {
                    continue;
                }
                if (in_array($k, ['send_type', 'business_time_type_week', 'business_time_type_day'])) {
                    $newItem = json_encode($item, true);
                } else {
                    $newItem = $item;
                }
                if ($k == 'web_service_url') {
                    $newItem = urlencode($item);
                }
                if ($k == 'web_url') {
                    $newItem = urlencode($item);
                }
                $mallSetting = MallSetting::findOne(['key' => $k, 'mall_id' => \Yii::$app->mall->id]);
                if ($mallSetting) {
                    if (!$newItem) {
                        $newItem = '';
                    }
                    $mallSetting->value = (string)$newItem;
                    $res = $mallSetting->save();
                } else {
                    if (!$newItem) {
                        $newItem = '';
                    }
                    $mallSetting = new MallSetting();
                    $mallSetting->key = $k;
                    $mallSetting->value = (string)$newItem;
                    $mallSetting->mall_id = \Yii::$app->mall->id;
                    $res = $mallSetting->save();
                }
                if (!$res) {
                    throw new \Exception($this->responseErrorMsg($mallSetting));
                }
            }
            \Yii::$app->mall->attributes = $this->attributes;
            if (!\Yii::$app->mall->save()) {
                throw new \Exception('保存失败,商城数据异常');
            }
            $transaction->commit();
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '保存成功。',
            ];
        } catch (\Exception $e) {
            $transaction->rollBack();
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg' => $e->getMessage(),
                'error' => [
                    'line' => $e->getLine(),
                ]
            ];
        }
    }
}
