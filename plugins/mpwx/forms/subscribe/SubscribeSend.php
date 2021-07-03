<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 订阅消息发送
 * Author: zal
 * Date: 2020-04-14
 * Time: 14:50
 */

namespace app\plugins\mpwx\forms\subscribe;


use app\forms\common\template\TemplateSender;
use app\plugins\mpwx\models\WxappSubscribe;
use app\plugins\mpwx\Plugin;

/**
 * Class SubscribeForm
 * @package app\plugins\mpwx\forms\subscribe
 * @property Plugin $plugin
 */
class SubscribeSend extends TemplateSender
{
    private $mallId;
    protected $plugin;
    public $is_need_form_id = false;

    public function init()
    {
        parent::init(); // TODO: Change the autogenerated stub
        $this->plugin = new Plugin();
    }

    /**
     * 微信发送订阅消息
     * @param array $arg
     * @return array
     * @throws \Exception
     */
    public function sendTemplate($arg = array())
    {
        $arg = $this->adaptive($arg);
        $this->mallId = $arg['user']->mall_id;
        $subscribe = $this->plugin->getSubscribe();
        if (isset($arg['templateId']) && $arg['templateId']) {
            $templateId = $arg['templateId'];
        } else {
            if (!isset($arg['templateTpl'])) {
                throw new \Exception('无效的templateTpl或templateId');
            }
            $wxappTemplate = WxappSubscribe::findOne([
                'tpl_name' => $arg['templateTpl'],
                'mall_id' => $this->mallId,
            ]);
            if ($wxappTemplate) {
                $templateId = $wxappTemplate->tpl_id;
            } else {
                $templateId = $this->getTemplateId($arg['templateTpl']);
            }
        }
        $res = $subscribe->send([
            'touser' => $arg['user']->userInfo->platform_user_id,
            'template_id' => $templateId,
            'page' => $arg['page'],
            'data' => $arg['data'],
        ]);
        return $res;
    }

    protected function adaptive($arg)
    {
        $change = $this->change();
        if (isset($arg['templateTpl']) && isset($change[$arg['templateTpl']])) {
            $arg['templateTpl'] = $change[$arg['templateTpl']];
        }
        if (!isset($arg['dataKey']) || empty($arg['dataKey'])) {
            $model = new SubscribeForm();
            $default = $model->getTemplateInfo();
            $dataKey = isset($default[$arg['templateTpl']]) ? $default[$arg['templateTpl']]['data'] : [];
        } else {
            if (isset($arg['dataKey']['wxapp'][$arg['templateTpl']])) {
                $dataKey = $arg['dataKey']['wxapp'][$arg['templateTpl']]['data'];
            } else {
                throw new \Exception('wxapp不支持' . $arg['templateTpl'] . '模板消息发送');
            }
        }
        $newData = [];
        $count = 1;
        foreach ($dataKey as $index => $item) {
            $value = '';
            if (isset($arg['data']['keyword' . $count])) {
                $value = $arg['data']['keyword' . $count]['value'];
            }
            $value = $this->checkData($index, $value);
            $count++;
            $newData[$index] = [
                'value' => $value
            ];
        }
        $arg['data'] = $newData;
        return $arg;
    }

    /**
     * 模板消息转化
     */
    protected function change()
    {
        return [
            'enroll_error_tpl' => 'enroll_success_tpl', // 报名失败通知 => 活动状态通知
            'share_audit_tpl' => 'audit_result_tpl', // 分销商审核状态通知 => 审核结果通知
        ];
    }

    /**
     * 获取template_id
     * @param $templateTpl
     * @return string
     * @throws \Exception
     */
    private function getTemplateId($templateTpl)
    {
        $model = new SubscribeForm();
        return $model->addTemplateOne($templateTpl);
    }

    /**
     * 检测数据
     * @param string $key 参数键值
     * @param string $value
     * @return string
     */
    public function checkData($key, $value)
    {
        if (preg_match('/^[a-z]+[_]?[a-z]+/i', $key, $arr)) {
            switch ($arr[0]) {
                case 'thing': // 20个以内字符 可汉字、数字、字母或符号组合
                    $value = mb_substr($value, 0, 20);
                    break;
                case 'number': // 32位以内数字 只能数字，可带小数
                    $value = mb_substr($value, 0, 32);
                    break;
                case 'letter': // 32位以内字母 只能字母
                    $value = mb_substr($value, 0, 32);
                    break;
                case 'symbol': // 5位以内符号 只能符号
                    $value = mb_substr($value, 0, 5);
                    break;
                case 'character_string': // 32位以内数字、字母或符号 可数字、字母或符号组合
                    $value = preg_replace('/[\x{4e00}-\x{9fff}]/u', '', $value);
                    $value = mb_substr($value, 0, 32);
                    break;
                case 'time': // 24小时制时间格式（支持+年月日） 例如：15:01，或：2019年10月1日 15:01
                    break;
                case 'date': // 年月日格式（支持+24小时制时间） 例如：2019年10月1日，或：2019年10月1日 15:01
                    break;
                case 'amount': // 1个币种符号+10位以内纯数字，可带小数，结尾可带“元” 可带小数
                    break;
                case 'phone_number': // 17位以内，数字、符号 电话号码，例：+86-0766-66888866
                    $value = mb_substr($value, 0, 17);
                    break;
                case 'car_number': // 8位以内，第一位与最后一位可为汉字，其余为字母或数字 车牌号码：粤A8Z888挂
                    $value = mb_substr($value, 0, 8);
                    break;
                case 'name': // 10个以内纯汉字或20个以内纯字母或符号 中文名10个汉字内；纯英文名20个字母内；中文和字母混合按中文名算，10个字内
                    $value = preg_replace('/[0-9]/u', '', $value);
                    $max = 20;
                    if (preg_match('/[\x{4e00}-\x{9fff}]/u', $value)) {
                        $max = 10;
                    }
                    $value = mb_substr($value, 0, $max);
                    break;
                case 'phrase': // 5个以内汉字 5个以内纯汉字，例如：配送中
                    $value = preg_replace('/[^\x{4e00}-\x{9fff}]/u', '', $value);
                    $value = mb_substr($value, 0, 5);
                    break;
            }
        }
        return $value;
    }
}
