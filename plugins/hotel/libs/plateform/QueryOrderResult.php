<?php


namespace app\plugins\hotel\libs\plateform;


class QueryOrderResult
{
    const CODE_SUCC = 0;
    const CODE_FAIL = 1;

    public $code = 0;
    public $message;

    public $plateform_order_no = null; //第三方平台单号
    public $order_state = 0; //订单状态：0待确认 1预订成功 2已取消 3预订未到 4已入住 5已完成 6确认失败
    public $pay_state = 0; //支付状态：支付状态 0未付款 1已付款 2退款处理中 3已退款
    public $pay_type = -1; //支付方式（0 到付 1 线上预付）

    public $origin_data = null; //原始数据
}