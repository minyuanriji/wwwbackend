<?php

namespace app\plugins\oil\plateform;

use app\plugins\oil\models\OilOrders;
use app\plugins\oil\models\OilPlateforms;
use app\plugins\oil\models\OilProduct;

abstract class BasePlateform
{
    protected $platModel;

    public function __construct(OilPlateforms $platModel)
    {
        $this->platModel = $platModel;
    }

    abstract public function submit(OilOrders $order, OilProduct $product);
}