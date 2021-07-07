<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 订单取消通知
 * Author: zal
 * Date: 2020-04-14
 * Time: 14:50
 */

namespace app\forms\common\template\tplmsg;

/**
 * Class OrderCancelTemplate
 * @package app\forms\common\template\tplmsg
 * 订单取消通知
 */
class OrderCancelTemplate extends BaseTemplate
{
    protected $templateTpl = 'order_cancel_tpl';
    public $goodsName;
    public $order_no;
    public $price;
    public $remark;

    public function msg()
    {
        $data = [
            'keyword1' => [
                'value' => $this->goodsName,
                'color' => '#333333',
            ],
            'keyword2' => [
                'value' => $this->order_no,
                'color' => '#333333',
            ],
            'keyword3' => [
                'value' => $this->price,
                'color' => '#333333',
            ],
            'keyword4' => [
                'value' => $this->remark,
                'color' => '#333333',
            ],
        ];
        return $data;
    }

    public function test()
    {
        $this->goodsName = '测试商品';
        $this->order_no = 'c927467';
        $this->price = '￥1000.00';
        $this->remark = '订单超时未支付';
        return $this->send();
    }

    public function setTemplateForm()
    {
        $this->templateForm = null;
    }
}
