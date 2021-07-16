<?php

namespace app\plugins\addcredit\plateform\sdk\k_default;

use app\plugins\addcredit\models\AddcreditOrder;
use app\plugins\addcredit\plateform\IOrder;
use app\plugins\hotel\libs\bestwehotel\plateform_action\QueryOrderAction;
use app\plugins\hotel\libs\bestwehotel\plateform_action\SubmitOrderAction;

class PlateForm implements IOrder
{

    public function submit(AddcreditOrder $orderModel)
    {
        return (new SubmitOrderAction([
            'hotelOrder' => $orderModel,
            'plateform_class' => get_class($this)
        ]))->run();

    }

    public function query(AddcreditOrder $orderModel)
    {
        return (new QueryOrderAction([
            'hotelOrder' => $orderModel,
            'plateform_class' => get_class($this)
        ]))->run();
    }

}