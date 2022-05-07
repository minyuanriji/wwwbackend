<?php
namespace app\plugins\alibaba\exception;

use Throwable;

class AlibabaOrderException extends \Exception
{
    public $mall_id;
    public $order_id;
    public $order_detail_id;
    public $order_detail_1688_id;

}