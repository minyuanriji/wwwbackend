<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 云库存补货通知消息
 * Author: zal
 * Date: 2020-09-29
 * Time: 15:11
 */

namespace app\plugins\stock\helpers;

use Overtrue\EasySms\Contracts\GatewayInterface;
use Overtrue\EasySms\Message;

class StockFillMessage extends Message
{
    protected $goods_id;
    protected $num;
    protected $fillTime;
    protected $user_id;

    protected $smsConfig;
    // protected $strategy = OrderStrategy::class;           // 定义本短信的网关使用策略，覆盖全局配置中的 `default.strategy`
    // protected $gateways = ['alidayu', 'yunpian', 'juhe']; // 定义本短信的适用平台，覆盖全局配置中的 `default.gateways`

    public function __construct($goods_id,$num,$fillTime,$smsConfig)
    {
        $this->goods_id = $goods_id;
        $this->num = $num;
        $this->fillTime = $fillTime;
        $this->smsConfig = $smsConfig;
    }

    // 定义直接使用内容发送平台的内容
    public function getContent(GatewayInterface $gateway = null)
    {
        return sprintf('您有一条新的补货提醒，补货商品编号：%s，补货数量：%s，补货时长：%s小时，请您在补货时长内及时补货。', $this->goods_id,$this->num,$this->fillTime);
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
            $this->smsConfig['template_variable_id'] => $this->goods_id,
            $this->smsConfig['template_variable_num'] => $this->num,
            $this->smsConfig['template_variable_duration'] => $this->fillTime
        ];
    }
}
