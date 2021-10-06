<?php

namespace app\plugins\addcredit\plateform;

use app\plugins\addcredit\models\AddcreditOrder;
use app\plugins\addcredit\models\AddcreditPlateforms;
use app\plugins\addcredit\plateform\result\GoodsDetailResult;
use app\plugins\addcredit\plateform\result\CreateOrderResult;
use app\plugins\addcredit\plateform\result\QueryResult;
use app\plugins\addcredit\plateform\result\SubmitResult;
use app\plugins\addcredit\plateform\result\AccessToken;

interface IOrder
{
    /**
     * 获取access_token
     * @param  $app_id
     * @param  $app_key
     * @return AccessToken
     */
    public function getAccessToken($app_id, $app_key);

    /**
     * 获取商品详情
     * @param AddcreditPlateforms $plateform
     * @return GoodsDetailResult
     */
    public function getGoodsDetail(AddcreditPlateforms $plateform);

    /**
     * 商品下单接口
     * @param $params
     * @return  CreateOrderResult
     */
    public function getCreateOrder($params);

    /**
     * 提交订单
     * @param AddcreditOrder $orderModel
     * @param AddcreditPlateforms $plateform
     * @return SubmitResult
     */
    public function submit(AddcreditOrder $orderModel, AddcreditPlateforms $plateform);

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