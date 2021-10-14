<?php

namespace app\plugins\addcredit\plateform\sdk\jing36;

use app\plugins\addcredit\models\AddcreditOrder;
use app\plugins\addcredit\models\AddcreditPlateforms;

abstract class BaseAction
{
    public $orderModel;
    public $plateModel;

    public function __construct(AddcreditOrder $addcreditOrder, AddcreditPlateforms $plateform){
        $this->orderModel = $addcreditOrder;
        $this->plateModel = $plateform;
    }

    abstract public function run();
}