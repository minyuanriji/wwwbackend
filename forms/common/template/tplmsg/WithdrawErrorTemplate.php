<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 提现失败通知
 * Author: zal
 * Date: 2020-04-14
 * Time: 14:50
 */

namespace app\forms\common\template\tplmsg;

/**
 * Class WithdrawErrorTemplate
 * @package app\forms\common\template\tplmsg
 * 提现失败
 */
class WithdrawErrorTemplate extends BaseTemplate
{
    public $price; // 提现金额
    public $remark; // 失败原因
    protected $templateTpl = 'withdraw_error_tpl';

    public function msg()
    {
        return [
            'keyword1' => [
                'value' => $this->price,
                'color' => '#333333',
            ],
            'keyword2' => [
                'value' => $this->remark,
                'color' => '#333333',
            ],
        ];
    }

    public function test()
    {
        $this->price = '200';
        $this->remark = '点此查看详情';
        return $this->send();
    }

    public function setTemplateForm()
    {
        $this->templateForm = null;
    }
}
