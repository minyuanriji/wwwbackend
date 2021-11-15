<?php

namespace app\plugins\alibaba\helpers;

use app\plugins\alibaba\models\AlibabaApp;
use app\plugins\alibaba\models\AlibabaDistributionOrderDetail;
use app\plugins\alibaba\models\AlibabaDistributionOrderDetail1688;
use lin010\alibaba\c2b2b\api\GetOrderInfo;
use lin010\alibaba\c2b2b\api\GetOrderInfoResponse;
use lin010\alibaba\c2b2b\api\GetRefundReasonList;
use lin010\alibaba\c2b2b\api\GetRefundReasonListResponse;
use lin010\alibaba\c2b2b\Distribution;

class AliRefundHelper
{
    public static function getReasonList(AlibabaDistributionOrderDetail $orderDetail,
                AlibabaDistributionOrderDetail1688 $orderDetail1688){

        $cacheKey = "AlibabaOrderRefundReasonList";
        $cache = \Yii::$app->getCache();

        $cacheData = $cache->get($cacheKey);
        if(!empty($cacheData)){
            $reasons = $cacheData;
        }else{
            $app = AlibabaApp::findOne($orderDetail->app_id);
            $distribution = new Distribution($app->app_key, $app->secret);

            //获取货物状态
            $res = $distribution->requestWithToken(new GetOrderInfo([
                "webSite" => "1688",
                "orderId" => $orderDetail1688->ali_order_id
            ]), $app->access_token);
            if(!empty($res->error)){
                throw new \Exception($res->error);
            }
            if(!$res instanceof GetOrderInfoResponse){
                throw new \Exception("[GetOrderInfoResponse]返回结果异常");
            }
            $status = isset($res->result['baseInfo']) ? $res->result['baseInfo']['status'] : null;
            if(!$status || in_array($status, ["waitbuyerpay", "waitsellersend"])){
                $afterSaleStatus = "refundWaitSellerSend";
            }elseif(in_array($status, ["waitbuyerreceive"])){
                $afterSaleStatus = "refundWaitBuyerReceive";
            }else{
                $afterSaleStatus = "refundBuyerReceived";
            }

            //获取退款原因
            $res = $distribution->requestWithToken(new GetRefundReasonList([
                "orderId"       => $orderDetail1688->ali_order_id,
                "orderEntryIds" => json_encode([$orderDetail1688->ali_order_id]),
                "goodsStatus"   => $afterSaleStatus
            ]), $app->access_token);
            if(!empty($res->error)){
                throw new \Exception($res->error);
            }
            if(!$res instanceof GetRefundReasonListResponse){
                throw new \Exception("[GetRefundReasonListResponse]返回结果异常");
            }

            $reasons = $res->reasons;
            if($reasons){
                $cache->set($cacheKey, $reasons);
            }

        }

        return $reasons ? $reasons : [];
    }
}