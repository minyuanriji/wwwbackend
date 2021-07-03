<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 账户变动通知
 * Author: zal
 * Date: 2020-04-14
 * Time: 14:50
 */

namespace app\forms\common\template\tplmsg;

/**
 * 账户变动通知
 * Class AccountChange
 * @package app\forms\common\template\tplmsg
 *
 */
class AccountChange extends BaseTemplate
{
    public $remark; // 备注
    public $desc; // 变动原因
    protected $templateTpl = 'account_change_tpl';

    /**
     * TODO 账户余额变动模板消息废弃
     * @return mixed
     * @throws \Exception
     */
    public function msg()
    {
        return [
            'keyword1' => [
                'value' => $this->remark,
                'color' => '#333333',
            ],
            'keyword2' => [
                'value' => $this->desc,
                'color' => '#333333',
            ],
        ];
    }

    public function test()
    {
        $this->remark = '测试';
        $this->desc = '测试';
        return $this->send();
    }

    public function setTemplateForm()
    {
        $this->templateForm = null;
    }
}
