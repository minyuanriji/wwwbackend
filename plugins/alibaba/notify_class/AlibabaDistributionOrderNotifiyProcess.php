<?php

namespace app\plugins\alibaba\notify_class;

use app\core\payment\PaymentNotify;
use app\core\payment\PaymentOrder;
use app\models\UserAddress;
use app\plugins\alibaba\forms\api\AlibabaDistributionOrderForm;
use app\plugins\alibaba\models\AlibabaApp;
use app\plugins\alibaba\models\AlibabaDistributionGoodsList;
use app\plugins\alibaba\models\AlibabaDistributionOrder;
use app\plugins\alibaba\models\AlibabaDistributionOrderDetail;
use lin010\alibaba\c2b2b\api\OrderGetPreview;
use lin010\alibaba\c2b2b\api\OrderGetPreviewResponse;
use lin010\alibaba\c2b2b\Distribution;

class AlibabaDistributionOrderNotifiyProcess extends PaymentNotify{


    /**
     * @param PaymentOrder $paymentOrder
     * @return mixed
     */
    public function notify($paymentOrder){

        $order = AlibabaDistributionOrder::findOne([
            "order_no" => $paymentOrder->orderNo
        ]);

        if(!$order || $order->is_pay){
            return;
        }

        $order->is_pay = 1;
        switch ($paymentOrder->payType) {
            case PaymentOrder::PAY_TYPE_HUODAO:
                $order->is_pay = 0;
                $order->pay_type = 2;
                break;
            case PaymentOrder::PAY_TYPE_BALANCE:
                $order->pay_type = 3;
                break;
            case PaymentOrder::PAY_TYPE_WECHAT:
                $order->pay_type = 1;
                break;
            case PaymentOrder::PAY_TYPE_ALIPAY:
                $order->pay_type = 4;
                break;
            case PaymentOrder::PAY_TYPE_BAIDU:
                $order->pay_type = 5;
                break;
            case PaymentOrder::PAY_TYPE_TOUTIAO:
                $order->pay_type = 6;
                break;
            default:
                break;
        }
        $order->pay_at = time();
        $order->save();

        //通知阿里巴巴下单
        $query = AlibabaDistributionOrderDetail::find()->alias("od")->where([
            "order_id" => $order->id
        ])->innerJoin(["a" => AlibabaApp::tableName()], "a.id=od.app_id")
          ->innerJoin(["g" => AlibabaDistributionGoodsList::tableName()], "g.id=od.goods_id");
        $orderDetails = $query->select(["od.*", "a.app_key", "a.secret", "a.access_token", "g.ali_offerId"])->asArray()->all();

        $userAddress = UserAddress::findOne($order->address_id);
        $aliAddress = (array)@json_decode($order->ali_address_info, true);

        foreach($orderDetails as $orderDetail){
            $distribution = new Distribution($orderDetail['app_key'], $orderDetail['secret']);
            $previewData = AlibabaDistributionOrderForm::getAliOrderPreviewData($distribution, $orderDetail['access_token'], [
                'offerId'   => $orderDetail['ali_offerId'],
                'specId'    => $orderDetail['ali_spec_id'],
                'quantity'  => $orderDetail['num']
            ], $userAddress, $aliAddress);
            print_r($previewData);
            exit;
        }

        return true;
    }
}