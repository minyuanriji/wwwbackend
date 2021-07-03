<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 微信模板消息
 * Author: xuyaoxiang
 * Date: 2020/10/7
 * Time: 10:38
 */

namespace app\services\wechat;
//注意小写

use app\logic\OptionLogic;
use app\models\Option;
use app\models\TemplateMessage;
use app\models\UserInfo;
use app\models\UserSetting;
use app\models\Wechat;
use app\models\WechatTempNoticeLog;
use Yii;
use app\services\ReturnData;
use app\services\MallSetting\MallSettingService;
use yii\log\Logger;
use EasyWeChat\Factory;
/**
 *
 * Class WechatTemplateService
 * @package app\services\wechat
 */
class WechatTemplateService
{
    use ReturnData;

    private $app;

    private $mall_id;

    private $is_open = false; //是否开启推送

    private $is_logging = false; //是否开启日志

    private $is_miniapp_priority = false; //是否跳转链接优先跳小程序

    private $user_id; //用户名

    private $template_key; //模板key

    private $url; //公众号链接

    private $miniapp_page; //小程序页面

    private $platform; //跳转平台

    private $send_data; //发送的模板数据

    //目前支持25个，最多
    const TEM_KEY = [
        'order_create' => ['tem_key' => 'OPENTM401424715', 'name' => '订单生成通知'],

        'order_pay_success' => ['tem_key' => 'OPENTM201285651', 'name' => '订单支付成功'],

        'order_cancel' => ['tem_key' => 'OPENTM409879210', 'name' => '订单取消通知'],

        'order_sent' => ['tem_key' => 'OPENTM411672602', 'name' => '订单发货提醒'],

        'order_confirmed' => ['tem_key' => 'OPENTM401097071', 'name' => '订单确认收货'],

        'group_buy_success' => ['tem_key' => 'OPENTM417184608', 'name' => '拼团成功通知'],

        'group_buy_failed' => ['tem_key' => 'OPENTM407446753', 'name' => '拼团失败通知'],

        'balance_change' => ['tem_key' => 'OPENTM204526957', 'name' => '账户余额通知'],

        'income_change' => ['tem_key' => 'OPENTM204526957', 'name' => '账户余额通知'], //共用一个模板

        'score_change' => ['tem_key' => 'TM00772', 'name' => '积分交易提醒'],

        'order_refund_agree' => ['tem_key' => 'OPENTM406409806', 'name' => '退货申请通过提醒'],

        'order_refund_refuse' => ['tem_key' => 'OPENTM417999034', 'name' => '售后申请审核通知'],

        'order_refund_money'=>['tem_key'=>'OPENTM202723917','name'=>'退款成功通知'],

        'userRegister' => ['tem_key' => 'OPENTM207762442', 'name' => '下线加入通知'],

        'upLevel' => ['tem_key' => 'OPENTM416122035', 'name' => '会员等级变更通知'],//会员升级
    ];

    const PLATFORM_MP_MX = 'mp-wx'; //小程序

    const PLATFORM_WECHAT = 'wechat'; //公众号

    const INDUSTRY_CONSUMER_GOODS = 31; //消费品 默认行业消费品,id:31

    const ERRCODE_INVALID_TEMPLATE_ID = 40037; //微信公众号没有该模板id

    const MALL_SETTING_KEY = 'wechat_notice'; //商城配置键

    const USER_SETTING_KEY = 'wechat_notice'; //用户配置键

    public function __construct($mall_id)
    {
        $this->mall_id = $mall_id;

        //微信app_id配置
        $WechatParmasService = new WechatParmasService();
        $WechatParmasService->setWechatParmas($this->mall_id);

        $this->app =   Factory::officialAccount(\Yii::$app->params['wechatConfig']);

        $this->setMallSetting();
    }


    /**
     * 获取商城设置
     * @return array
     */
    public function getMallSetting()
    {
        return [
            'is_open'             => $this->is_open,
            'is_miniapp_priority' => $this->is_miniapp_priority,
            'is_logging'          => $this->is_logging
        ];
    }

    /**
     * @param $touser
     * @param $template_id
     * @param $url
     * @param $data
     * [
     * 'touser'      => 'owdRztxk_XOtVZ0xglXuGv_MsRbA',
     * 'template_id' => '7rTBQadzdA4qn2RSR0_6lYUJxBlR6HloEMl03AuPRK4',
     * 'url'         => 'http://jxmall.com',
     * 'data'        => [
     * 'productType' => '商品名称',
     * 'name'        => '小米手机',
     * 'number'      => 12,
     * 'expDate'     => '永久有效',
     * 'remark'      => '恭喜你，购买成功'
     * ],
     * ]
     *
     * @return array
     * {
     * "errcode": 0,
     * "errmsg": "ok",
     * "msgid": 1552413574407520257
     * }
     */
    public function sendWechat($touser, $template_id, $url, $data)
    {
        $app = $this->app;

        return $app->template_message->send([
            'touser'      => $touser,
            'template_id' => $template_id,
            'url'         => $url,
            'data'        => $data
        ]);
    }

    /**
     * 初始化商城设置
     */
    private function setMallSetting()
    {
        $MallSettingService        = new MallSettingService($this->mall_id);
        $data                      = $MallSettingService->getValueByKey(self::MALL_SETTING_KEY);
        $this->is_miniapp_priority = isset($data['is_miniapp_priority']) ? $data['is_miniapp_priority'] : 0;
        $this->is_open             = isset($data['is_open']) ? $data['is_open'] : 0;
        $this->is_logging          = isset($data['is_logging']) ? $data['is_logging'] : 0;
    }

    /**
     * @param $touser
     * @param $template_id
     * @param $url
     * @param $data
     * [
     * 'touser' => 'user-openid',
     * 'template_id' => 'template-id',
     * 'url' => 'https://abc.org',
     * 'miniprogram' => [
     * 'appid' => 'xxxxxxx',
     * 'pagepath' => 'pages/xxx',
     * ],
     * 'data' => [
     * 'key1' => 'VALUE',
     * 'key2' => 'VALUE2',
     * ...
     * ],
     * ]
     * @param $pagepath
     */
    public function sendMiniApp($touser, $template_id, $url, $data, $pagepath)
    {
        $app = $this->app;

        $app_id = isset(\Yii::$app->params['wechatMiniProgramConfig']['app_id']) ? \Yii::$app->params['wechatMiniProgramConfig']['app_id'] : "";

        return $app->template_message->send([
            'touser'      => $touser,
            'template_id' => $template_id,
            'url'         => $url,
            'miniprogram' => [
                'appid'    => $app_id,
                'pagepath' => $pagepath,
            ],
            'data'        => $data
        ]);
    }

    /**
     * 推送到微信公众号
     * @param $user_id
     * @param $template_key
     * @param $url
     * @param $data
     * @param $platform
     * @param null $miniapp_page
     * @return array
     */
    public function send($user_id, $template_key, $url, $data, $platform = "wechat", $miniapp_page = null)
    {
        if (!$this->is_open) {
            return $this->returnApiResultData(90, "商城没有开启推送配置");
        }

        if (!$this->getUserSetting($user_id, $this->mall_id)) {
            return $this->returnApiResultData(91, "用户没有开启接收推送");
        }

        $user_info = $this->getUserInfo($user_id);

        if (!$user_info) {
            return $this->returnApiResultData(99, "公众号用户不存在,platform:" . $platform);
        }

        $TemplateMessage   = new TemplateMessage();
        $params['tempkey'] = $template_key;
        $item              = $TemplateMessage->setMallId($this->mall_id)->getOne($params);

        //如果数据库没有对应模板，请求微信接口自动添加
        if (!$item) {
            $TemplateMessage = new TemplateMessage();
            $return          = $TemplateMessage->setMallId($this->mall_id)->addTemplate($template_key);
            if ($return['code'] > 0) {
                return $this->returnApiResultData($return['code'], $return['msg']);
            }
            $item = $return['data'];
        }

        //消息链接跳转到小程序
        if ($platform == self::PLATFORM_MP_MX) {

            $return = $this->sendMiniApp($user_info->openid, $item->tempid, $url, $data, $miniapp_page);
        }

        //消息链接跳转到公众号
        if ($platform == self::PLATFORM_WECHAT) {

            $return = $this->sendWechat($user_info->openid, $item->tempid, $url, $data);
        }

        //日志
        $log['result'] = json_encode($return);
        $log['params'] = json_encode([
            'user_id'      => $user_id,
            'template_key' => $template_key,
            'url'          => $url,
            'data'         => $data,
            'platform'     => $platform,
            'miniapp_page' => $miniapp_page,
        ]);

        $this->logging($log);

        //微信公众号数据没有该模板,删除本地数据库中的数据
        if ($return['errcode'] == self::ERRCODE_INVALID_TEMPLATE_ID) {
            $TemplateMessage   = new TemplateMessage();
            $params['tempkey'] = $template_key;
            $TemplateMessage->setMallId($this->mall_id)->delTemplateMessage($params);
        }

        return $this->returnApiResultData($return['errcode'], $return['errmsg']);
    }

    public function setUserId($user_id){
        $this->user_id=$user_id;

        return $this;
    }

    public function setTemplateKey($template_key){
        $this->template_key=$template_key;

        return $this;
    }

    public function setUrl($url){
        $this->url=$url;

        return $this;
    }

    public function setMiniappPage($miniapp_page){
        $this->miniapp_page=$miniapp_page;

        return $this;
    }

    public function setSendData($send_data){
        $this->send_data=$send_data;

        return $this;
    }

    public function sendV2()
    {
        $user         = $this->user_id;
        $template_key = $this->template_key;
        $url          = $this->url;
        $miniapp_page = $this->miniapp_page;
        $platform     = $this->getPlatForm();
        $send_data    = $this->send_data;

        return $this->send($user, $template_key, $url, $send_data, $platform, $miniapp_page);
    }

    public function logging($log)
    {
        if ($this->is_logging) {
            $WechatTempNoticeLog             = new WechatTempNoticeLog();
            $WechatTempNoticeLog->attributes = $log;
            $WechatTempNoticeLog->created_at = time();
            $WechatTempNoticeLog->save();
        }
    }

    /**
     * 用户openid
     * @param $user_id
     * @return array|\yii\db\ActiveRecord|null
     */
    public function getUserInfo($user_id)
    {
        return UserInfo::find()->where(['deleted_at' => 0, 'platform' => self::PLATFORM_WECHAT, 'user_id' => $user_id])->one();
    }

    /**
     * 获取模板
     */
    public function getPrivateTemplates()
    {
        $app = $this->app;

        $app->template_message->getPrivateTemplates();
    }

    /**
     * 链接跳转到哪个平台
     * @return string
     */
    public function getPlatForm()
    {
        if ($this->is_miniapp_priority == 1) {
            return 'mp-wx';
        } else {
            return 'wechat';
        }
    }

    /**
     * 获取用户推送配置
     * @param $user_id
     * @param $mall_id
     * @return bool
     */
    public function getUserSetting($user_id, $mall_id)
    {
        $UserSetting           = new UserSetting();
        $params['setting_key'] = self::USER_SETTING_KEY;
        $params['user_id']     = $user_id;
        $params['mall_id']     = $mall_id;
        $return                = $UserSetting->getOne($params);
        if (0 == $return['code']) {
            return boolval($return['data']['data']['is_open']);
        }

        return true;
    }
}