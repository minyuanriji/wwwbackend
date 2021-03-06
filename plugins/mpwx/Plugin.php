<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 插件核心
 * Author: zal
 * Date: 2020-04-14
 * Time: 14:50
 */
namespace app\plugins\mpwx;
use app\plugins\mpwx\forms\subscribe\SubscribeForm;
use app\plugins\mpwx\models\WechatTemplate;
use app\plugins\mpwx\models\WxappSubscribe;
class Plugin extends \app\plugins\Plugin
{

    private $wechatTemplate;


    public function getIsSetToQuickMenu()
    {
        //这里去缓存里面查询

        return false; // TODO: Change the autogenerated stub
    }

    public function getMenuForMainMenu()
    {
        return [
            'key' => $this->getName(),
            'name' => '小程序管理',
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
                'name' => '基础配置',
                'route' => 'plugin/mpwx/mall/config/setting',
                'icon' => 'el-icon-setting',
            ],
//            [
//                'name' => '模板消息',
//                'route' => 'plugin/mpwx/mall/template-msg/setting',
//                'icon' => 'el-icon-setting',
//            ],
            [
                'name' => '小程序发布',
                'route' => 'plugin/mpwx/mall/app-upload',
                'icon' => 'el-icon-setting',
            ],
            [
                'name' => '单商户小程序',
                'route' => 'plugin/mpwx/mall/app-upload/no-mch',
                'icon' => 'el-icon-setting',
            ],
            [
                'name' => '群发消息',
                'route' => 'plugin/mpwx/mall/template-msg/send',
            ],
        ];
    }

    public function getIndexRoute()
    {
        return 'plugin/mpwx/mall/config/setting';
    }


    /**
     * 插件唯一id，小写英文开头，仅限小写英文、数字、下划线
     * @return string
     */
    public function getName()
    {
        return 'mpwx';
    }

    /**
     * 插件显示名称
     * @return string
     */
    public function getDisplayName()
    {
        return '微信小程序';
    }


    /**
     * @return array
     * 模板消息
     */
    public function templateInfoList()
    {
        return [
//            'order_pay_tpl' => [
//                'id' => 'AT0009',
//                'keyword_id_list' => [5, 6, 11, 4],
//                'title' => '订单支付成功通知'
//            ],
            'order_pay_tpl' => [
                'id' => 'AT0229',
                'keyword_id_list' => [2, 9, 1, 34],
                'title' => '下单成功通知'
            ],
            'order_cancel_tpl' => [
                'id' => 'AT0024',
                'keyword_id_list' => [24, 5, 4, 1],
                'title' => '订单取消通知'
            ],
            'order_send_tpl' => [
                'id' => 'AT0007',
                'keyword_id_list' => [5, 2, 23, 55],
                'title' => '订单发货提醒'
            ],
            'order_refund_tpl' => [
                'id' => 'AT0036',
                'keyword_id_list' => [33, 13, 3, 4],
                'title' => '退款通知'
            ],
//            'enroll_success_tpl' => [
//                'id' => 'AT0027',
//                'keyword_id_list' => [6, 5, 18],
//                'title' => '报名成功通知'
//            ],
            'enroll_success_tpl' => [
                'id' => 'AT0276',
                'keyword_id_list' => [8, 9, 10],
                'title' => '信息提交成功通知'
            ],
            'enroll_error_tpl' => [
                'id' => 'AT0028',
                'keyword_id_list' => [6, 1, 7],
                'title' => '报名失败通知'
            ],
            'account_change_tpl' => [
                'id' => 'AT0677',
                'keyword_id_list' => [1, 3],
                'title' => '账户变动提醒'
            ],
            'audit_result_tpl' => [
                'id' => 'AT0146',
                'keyword_id_list' => [33, 1],
                'title' => '审核结果通知'
            ],
            'withdraw_success_tpl' => [
                'id' => 'AT0830',
//                'keyword_id_list' => [5, 8, 4],
                'keyword_id_list' => [1, 2, 5, 3, 6],
                'title' => '提现到账通知'
            ],
            'withdraw_error_tpl' => [
                'id' => 'AT1242',
//                'keyword_id_list' => [3, 5],
                'keyword_id_list' => [5, 11, 3, 6],
                'title' => '提现失败通知'
            ],
            'share_audit_tpl' => [
                'id' => 'AT0674',
//                'keyword_id_list' => [2, 4],
                'keyword_id_list' => [1, 34, 6, 4],
                'title' => '审核状态通知'
            ],
        ];
    }

    /**
     * @return WechatTemplate
     * @throws \Exception
     * 微信模板消息发送
     */
    public function getWechatTemplate()
    {
        $this->wechatTemplate = new WechatTemplate([
            'accessToken' => $this->getAccessToken()
        ]);
        return $this->wechatTemplate;
    }

    //商品详情路径
    public static function getGoodsUrl($item)
    {
        return sprintf("/pages/goods/goods?id=%u", $item['id']);
    }


    /**
     * @return Wechat
     * @throws \luweiss\Wechat\WechatException
     */
    public function getWechat()
    {
        if ($this->wechat) {
            return $this->wechat;
        }
        $wxappConfig = WxappConfig::findOne(['mall_id' => \Yii::$app->mall->id]);
        if (!$wxappConfig || !$wxappConfig->appid || !$wxappConfig->appsecret) {
            throw new \Exception('小程序信息尚未配置。');
        }
        $this->wechat = new Wechat([
            'appId' => $wxappConfig->appid,
            'appSecret' => $wxappConfig->appsecret,
            'cache' => [
                'target' => Wechat::CACHE_TARGET_FILE,
                'dir' => \Yii::$app->runtimePath . '/wechat-cache',
            ],
        ]);
        return $this->wechat;
    }

    public function getIsPlatformPlugin()
    {
        return true;
    }

    /**
     * @param string|array $param
     * @return array|\yii\db\ActiveRecord[]|WxappSubscribe[]
     * 获取所有订阅消息
     */
    public function getTemplateList($param = '*')
    {
        $model = new SubscribeForm();

        return $model->getTemplateList($param);
    }

    /**
     * @param array $attributes
     * @return bool
     * @throws \Exception
     * 后台保存模板消息
     */
    public function addTemplateList($attributes)
    {
        $model = new SubscribeForm();
        return $model->addTemplateList($attributes);
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
