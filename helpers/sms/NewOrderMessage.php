<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 新订单消息
 * Author: zal
 * Date: 2020-04-18
 * Time: 15:11
 */

namespace app\helpers\sms;

use Overtrue\EasySms\Contracts\GatewayInterface;
use Overtrue\EasySms\Message;

class NewOrderMessage extends Message
{
    protected $order_id;
    protected $smsConfig;
    // protected $strategy = OrderStrategy::class;           // 定义本短信的网关使用策略，覆盖全局配置中的 `default.strategy`
    // protected $gateways = ['alidayu', 'yunpian', 'juhe']; // 定义本短信的适用平台，覆盖全局配置中的 `default.gateways`

    public function __construct($order_id, $smsConfig)
    {
        $this->order_id = $order_id;
        $this->smsConfig = $smsConfig;
        parent::__construct();
    }

    // 定义直接使用内容发送平台的内容
    public function getContent(GatewayInterface $gateway = null)
    {
        return sprintf("您有一条新的订单，订单号:%s", $this->order_id);
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
            $this->smsConfig['template_variable'] => $this->order_id
        ];
    }
}
