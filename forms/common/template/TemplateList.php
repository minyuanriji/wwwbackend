<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 模板消息列表
 * Author: zal
 * Date: 2020-04-14
 * Time: 14:50
 */

namespace app\forms\common\template;


use app\models\BaseModel;
use app\models\Mall;
use yii\helpers\ArrayHelper;

/**
 * Class TemplateList
 * @package app\forms\common\template
 * @property Mall $mall
 */
class TemplateList extends BaseModel
{
    public static $instance;
    public $mall;
    public $platform;

    public static function getInstance($mall = null)
    {
        if (!$mall) {
            $mall = \Yii::$app->mall;
        }
        if (!self::$instance) {
            self::$instance = new self([
                'mall' => $mall
            ]);
        }
        return self::$instance;
    }

    /**
     * 获取指定平台的模板消息列表
     * @param $platform
     * @return array
     * @throws \app\core\exceptions\ClassNotFoundException
     *
     */
    public function getList($platform)
    {
        $plugin = \Yii::$app->plugin->getPlugin($platform);
        $list = [];
        if (method_exists($plugin, 'getTemplateList')) {
            $list = $plugin->getTemplateList('tpl_name,tpl_id');
        }
        return $list;
    }

    /**
     * 获取指定平台的模板消息列表--测试模板消息发送
     * @param string $platform
     * @return array
     * @throws \app\core\exceptions\ClassNotFoundException
     *
     */
    public function getTemplateList($platform)
    {
        $list = $this->getList($platform);
        $arr = $this->getChineseName();
        foreach ($list as &$item) {
            $item = ArrayHelper::toArray($item);
            if (isset($arr[$item['tpl_name']])) {
                $item['name'] = $arr[$item['tpl_name']];
            }
        }
        unset($item);
        return $list;
    }

    /**
     * 获取所有的模板消息的名称
     * TODO 临时获取
     */
    protected function getChineseName()
    {
        return [
            'account_change_tpl' => '账户变动提醒',
            'enroll_error_tpl' => '报名失败通知',
            'enroll_success_tpl' => '活动状态通知',
            'order_cancel_tpl' => '订单取消通知',
            'order_pay_tpl' => '下单成功提醒',
            'order_refund_tpl' => '退款通知',
            'order_send_tpl' => '订单发货通知',
            'audit_result_tpl' => '审核结果通知',
            'share_audit_tpl' => '分销商审核状态通知',
            'withdraw_error_tpl' => '提现失败通知',
            'withdraw_success_tpl' => '提现成功通知',
            'sign_in_tpl' => '签到插件--签到提醒',
            'gift_to_user' => '礼物即将超时通知',
            'gift_convert' => '社交送礼--中奖结果通知',
            'gift_form_user' => '礼物未成功送达通知',
            'pay_advance_balance' => '尾款支付提醒',
            'bargain_success_tpl' => '砍价成功通知',
            'bargain_fail_tpl' => '砍价失败通知',
            'lottery_tpl' => '幸运抽奖--中奖结果通知',
            'mch_order_tpl' => '新订单通知',
            'spell_group_success_notice' => '拼团成功通知',
            'spell_group_fail_notice' => '拼团失败通知',
            'step_notice' => '步数宝--签到提醒',
            'vip_card_remind' => '会员卡到期提醒',
        ];
    }

    /**
     * 获取指定平台指定模板消息--前端获取订阅消息发送权限
     * @param $platform
     * @param array|string $tpl
     * @throws \app\core\exceptions\ClassNotFoundException
     * @return array
     *
     */
    public function getTemplate($platform, $tpl)
    {
        if (is_string($tpl)) {
            $tpl = [$tpl];
        }
        if (!is_array($tpl)) {
            throw new \Exception('tpl参数必须是数组或字符串');
        }
        $list = $this->getList($platform);
        $newList = [];
        foreach ($list as $item) {
            if (in_array($item['tpl_name'], $tpl)) {
                $newList[] = $item['tpl_id'];
            }
        }
        return $newList;
    }
}
