<?php
namespace app\mch\events;

use yii\base\Event;

class CheckoutOrderPaidEvent extends Event
{
    public $checkoutOrder;
    public $amount;

}