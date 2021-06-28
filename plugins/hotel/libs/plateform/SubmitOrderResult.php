<?php


namespace app\plugins\hotel\libs\plateform;


class SubmitOrderResult
{
    const CODE_SUCC = 0;
    const CODE_FAIL = 1;

    public $code = 0;
    public $message;

    public $plateform_order_no = null; //平台订单号
    public $is_success = false;  //是否预订成功

    public $originData = null;
}