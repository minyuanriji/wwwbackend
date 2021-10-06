<?php

namespace app\plugins\addcredit\plateform\sdk\qyj_sdk;

use app\plugins\addcredit\models\AddcreditOrder;
use app\plugins\addcredit\models\AddcreditPlateforms;
use app\plugins\addcredit\plateform\IOrder;

class PlateForm implements IOrder
{
    public function getAccessToken($app_id, $app_key)
    {
        return (new AccessTokenAction([
            'app_id' => $app_id,
            'app_key' => $app_key,
        ]))->run();
    }

    public function getGoodsDetail(AddcreditPlateforms $plateform)
    {
        return (new GoodsDetailAction([
            'AddcreditPlateforms' => $plateform,
        ]))->run();
    }

    public function getCreateOrder($params)
    {
        return (new CreateOrderAction([
            'params' => $params,
        ]))->run();
    }

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

    public function accountBalanceQuery($plateforms_params)
    {
        return (new AccountBalanceQueryAction([
            'plateforms_params' => $plateforms_params,
        ]))->run();
    }

}