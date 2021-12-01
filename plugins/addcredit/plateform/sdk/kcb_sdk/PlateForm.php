<?php

namespace app\plugins\addcredit\plateform\sdk\kcb_sdk;

use app\plugins\addcredit\models\AddcreditOrder;
use app\plugins\addcredit\models\AddcreditPlateforms;
use app\plugins\addcredit\plateform\IOrder;
use app\plugins\addcredit\plateform\result\QueryResult;

class PlateForm implements IOrder
{

    public function submit(AddcreditOrder $orderModel, AddcreditPlateforms $plateform)
    {
        return (new SubmitOrderAction([
            'AddcreditOrder'    => $orderModel,
            'AddcreditPlateforms' => $plateform,
        ]))->run();
    }

    public function query(AddcreditOrder $orderModel)
    {
        return (new QueryOrderAction([
            'AddcreditOrder' => $orderModel,
        ]))->run();
    }

    /**
     * 查询订单
     * @param AddcreditOrder $orderModel
     * @param AddcreditPlateforms $plateform
     * @return QueryResult
     */
    public function query2(AddcreditOrder $orderModel, AddcreditPlateforms $plateform)
    {
        return $this->query($orderModel);
    }
}