<?php
namespace app\plugins\addcredit\plateform;

use app\plugins\addcredit\models\AddcreditOrder;
use app\plugins\addcredit\plateform\result\QueryResult;
use app\plugins\addcredit\plateform\result\SubmitResult;

interface IOrder
{
    /**
     * 提交订单
     * @param AddcreditOrder $orderModel
     * @return SubmitResult
     */
    public function submit(AddcreditOrder $orderModel);

    /**
     * 查询订单
     * @param AddcreditOrder $orderModel
     * @return QueryResult
     */
    public function query(AddcreditOrder $orderModel);
}