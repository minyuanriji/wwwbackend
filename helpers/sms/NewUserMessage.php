<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 文件描述
 * Author: xuyaoxiang
 * Date: 2020/10/31
 * Time: 11:38
 */

namespace app\helpers\sms;

use Overtrue\EasySms\Contracts\GatewayInterface;
use Overtrue\EasySms\Message;


class NewUserMessage extends Message
{
    protected $nickname;
    protected $smsConfig;

    public function __construct($nickname, $smsConfig)
    {
        $this->nickname = $nickname;
        $this->smsConfig = $smsConfig;
        parent::__construct();
    }

    // 定义直接使用内容发送平台的内容
    public function getContent(GatewayInterface $gateway = null)
    {
        return sprintf("你有一个新用户注册,用户名:%s" ,$this->nickname);
    }

    // 定义使用模板发送方式平台所需要的模板 ID
    public function getTemplate(GatewayInterface $gateway = null)
    {
        return $this->smsConfig['template_id'];
    }

    // 模板参数
    public function getData(GatewayInterface $gateway = null)
    {
        return [
            $this->smsConfig['template_variable'] => $this->nickname
        ];
    }
}