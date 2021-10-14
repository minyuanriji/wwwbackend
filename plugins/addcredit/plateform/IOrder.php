<?php

namespace app\plugins\addcredit\plateform;

use app\plugins\addcredit\models\AddcreditOrder;
use app\plugins\addcredit\models\AddcreditPlateforms;
use app\plugins\addcredit\plateform\result\QueryResult;
use app\plugins\addcredit\plateform\result\SubmitResult;

interface IOrder
{
    /**
     * 提交订单
     * @param AddcreditOrder $orderModel
     * @param AddcreditPlateforms $plateform
     * @param $requestNum
     * @return SubmitResult
     */
    public function submit(AddcreditOrder $orderModel, AddcreditPlateforms $plateform, $requestNum);

    /**
     * 查询订单
     * @param AddcreditOrder $orderModel
     * @param AddcreditPlateforms $plateform
     * @return QueryResult
     */
    public function query2(AddcreditOrder $orderModel, AddcreditPlateforms $plateform);

    /**
     * 查询订单
     * @param AddcreditOrder $orderModel
     * @return QueryResult
     */
    public function query(AddcreditOrder $orderModel);

    /**
     * 账户余额查询
     * @param  $plateforms_params
     * @return QueryResult
     */
    public function accountBalanceQuery($plateforms_params);
}