<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 签到插件-签到提醒模板公共类
 * Author: zal
 * Date: 2020-04-20
 * Time: 14:10
 */

namespace app\plugins\sign_in\forms\common;


use app\forms\common\template\tplmsg\BaseTemplate;
use app\plugins\sign_in\forms\mall\TemplateForm;

/**
 * 签到提醒
 * Class CommonTemplate
 * @package app\plugins\sign_in\forms\common
 */
class CommonTemplate extends BaseTemplate
{

    protected $templateTpl = 'sign_in_tpl';
    /**
     * @return array
     * @throws \Exception
     * 每日签到提醒
     */
    public function msg()
    {
        $data = [
            'keyword1' => [
                'value' => '每日签到',
                'color' => '#333333'
            ],
            'keyword2' => [
                'value' => date('Y.m.d', time()) . ' 23:59:59',
                'color' => '#333333'
            ],
            'keyword3' => [
                'value' => '亲，您今天还没有签到哦',
                'color' => '#333333'
            ]
        ];
        return $data;
    }

    public function test()
    {
        return $this->send();
    }

    public function setTemplateForm()
    {
        $this->templateForm = new TemplateForm();
    }
}
