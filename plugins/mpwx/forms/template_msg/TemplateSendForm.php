<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 模板消息发送
 * Author: zal
 * Date: 2020-04-14
 * Time: 14:50
 */

namespace app\plugins\mpwx\forms\template_msg;

use app\forms\common\template\TemplateSender;
use app\plugins\mpwx\models\WxappTemplate;
use app\plugins\mpwx\Plugin;

class TemplateSendForm extends TemplateSender
{
    private $mallId;

    /**
     * 微信发送模板消息
     * @param array $data
     * @return array
     * @throws \Exception
     *
     */
    public function sendTemplate($data = array())
    {
        $plugin = new Plugin();
        $this->mallId = $data['user']->mall_id;
        $template = $plugin->getWechatTemplate();
        if (isset($data['templateId']) && $data['templateId']) {
            $templateId = $data['templateId'];
        } else {
            if (!isset($data['templateTpl'])) {
                throw new \Exception('无效的templateTpl或templateId');
            }
            $wxappTemplate = WxappTemplate::findOne([
                'tpl_name' => $data['templateTpl'],
                'mall_id' => $this->mallId,
            ]);
            if ($wxappTemplate) {
                $templateId = $wxappTemplate->tpl_id;
            } else {
                $templateId = $this->getTemplateId($plugin, $data);
            }
        }
        $res = $template->sendTemplateMessage([
            'touser' => $data['user']->userInfo->platform_user_id,
            'form_id' => $data['formId'],
            'template_id' => $templateId,
            'page' => $data['page'],
            'data' => $data['data'],
            'emphasis_keyword' => $data['titleStyle']
        ]);
        return $res;
    }

    /**
     * 获取template_id
     * @param Plugin $plugin
     * @param $data
     * @return mixed
     * @throws \Exception
     */
    private function getTemplateId(Plugin $plugin, $data)
    {
        $templateId = '';
        if (!$templateId) {
            $wechatTemplate = $plugin->getWechatTemplate();
            $templateInfoList = $plugin->templateInfoList();
            if (isset($templateInfoList[$data['templateTpl']])) {
                $params = $templateInfoList[$data['templateTpl']];
            } else {
                throw new \Exception('错误的模板消息参数');
            }
            // 微信小程序平台模板消息最多可添加数量
            $maxCount = 25;
            // 已查询数量
            $count = 0;
            while (true) {
                $list = $wechatTemplate->getTemplateList($count, 20);
                foreach ($list as $item) {
                    $count++;
                    if ($item['title'] == $params['title']) {
                        $templateId = $item['template_id'];
                        break;
                    }
                }
                if (!(!$templateId && $count == 20 && $count <= $maxCount)) {
                    break;
                }
            }
            if (!$templateId) {
                $res = $wechatTemplate->addTemplate($params['id'], $params['keyword_id_list']);
                $templateId = $res['template_id'];
            }
            if (!$templateId) {
                throw new \Exception('获取template_id错误');
            }
        }
        return $templateId;
    }
}
