<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 公众号模板数据发送
 * Author: zal
 * Date: 2020-04-22
 * Time: 19:20
 */

namespace app\forms\common\mptemplate;

use app\forms\common\CommonOption;
use app\logic\OptionLogic;
use app\models\Option;

class MpTplMsgDSend extends MpTplMsgSend
{
    private $option;
    public $key;

    public function getInfo(MpTplMsgSend $mp): Array
    {
        $this->getOption();
        $method = $mp->method;
        $templateMsg = $mp->model->$method([
            'template_id' => $this->getTemplate($method),
            'app_id' => '',
        ], $mp->params);
        $templateMsg['app_id'] = $this->option['app_id'];
        $templateMsg['app_secret'] = $this->option['app_secret'];
        $templateMsg['admin_open_list'] = $this->option['admin_open_list'];
        return $templateMsg;
    }

    private function getOption()
    {
        $option = OptionLogic::get(Option::NAME_WX_PLATFORM, \Yii::$app->mall->id, Option::GROUP_APP);
        if (!$option) {
            throw new \Exception('公众号配置不能为空');
        }
        $this->option = $option;
    }

    /**
     * TODO 命名必须规范
     * 驼峰命名转下划线
     */
    private function uncamelize($camelCaps, $separator = '_')
    {
        return strtolower(preg_replace('/([a-z])([A-Z])/', "$1" . $separator . "$2", $camelCaps));
    }

    private function getTemplate($method)
    {
        $templateId = false;
        foreach ($this->option['template_list'] as $k => $v) {
            if ($v['key_name'] == $this->uncamelize($method) && $v[$this->uncamelize($method)]) {
                $templateId = $v[$this->uncamelize($method)];
            }
        }
        if (!$templateId) {
            throw new \Exception('模板消息发送失败，模板尚未配置。');
        }
        return $templateId;
    }
}