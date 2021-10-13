<?php

namespace app\plugins\addcredit\plateform\sdk\jing36;

use app\plugins\addcredit\models\AddcreditOrder;
use app\plugins\addcredit\models\AddcreditPlateforms;
use app\plugins\addcredit\plateform\IOrder;
use app\plugins\addcredit\plateform\result\QueryResult;
use app\plugins\addcredit\plateform\result\SubmitResult;

class PlateForm implements IOrder
{

    /**
     * 提交订单
     * @param AddcreditOrder $addcreditOrder
     * @param AddcreditPlateforms $plateform
     * @param $requestNum
     * @return SubmitResult
     */
    public function submit(AddcreditOrder $addcreditOrder, AddcreditPlateforms $plateform, $requestNum = 1)
    {
        if($addcreditOrder->recharge_type == "fast"){ //快充
            $action = new SubmitFastAction($addcreditOrder, $plateform);
        }else{ //慢充

        }
        return $action->run();
    }

    /**
     * 查询订单
     * @param AddcreditOrder $orderModel
     * @return QueryResult
     */
    public function query(AddcreditOrder $orderModel)
    {
        // TODO: Implement query() method.
    }

    /**
     * 账户余额查询
     * @param  $plateforms_params
     * @return QueryResult
     */
    public function accountBalanceQuery($plateforms_params)
    {
        // TODO: Implement accountBalanceQuery() method.
    }
}