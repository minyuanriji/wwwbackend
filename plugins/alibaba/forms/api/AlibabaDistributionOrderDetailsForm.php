<?php

namespace app\plugins\alibaba\forms\api;

use app\core\ApiCode;
use app\logic\CommonLogic;
use app\models\BaseModel;
use app\plugins\alibaba\models\AlibabaApp;
use app\plugins\alibaba\models\AlibabaDistributionGoodsList;
use app\plugins\alibaba\models\AlibabaDistributionOrder;
use app\plugins\alibaba\models\AlibabaDistributionOrderDetail;
use app\plugins\alibaba\models\AlibabaDistributionOrderDetail1688;
use lin010\alibaba\c2b2b\api\GetOrderInfo;
use lin010\alibaba\c2b2b\api\GetOrderInfoResponse;
use lin010\alibaba\c2b2b\Distribution;

class AlibabaDistributionOrderDetailsForm extends BaseModel{

    public $order_id;

    public function rules()
    {
        return [
            [['order_id'], 'required'],
            [['order_id'], 'integer'],
        ];
    }

    /**
     * 获取订单详情
     * @return array
     * @throws \Exception
     */
    public function getOrderDetails()
    {
        try {
            if (!$this->validate()) {
                return $this->returnApiResultData();
            }

            $order = AlibabaDistributionOrder::findOne($this->order_id);
            if(!$order || $order->is_delete){
                throw new \Exception('[AlibabaDistributionOrder] 订单不存在');
            }

            $orderDetails = AlibabaDistributionOrderDetail::find()->where([
                "order_id"  => $order->id,
                "is_delete" => 0
            ])->all();
            if(!$orderDetails){
                throw new \Exception('[AlibabaDistributionOrderDetail] 订单详情不存在');
            }

            $query = AlibabaDistributionOrderDetail::find()->alias("od");
            $query->innerJoin(["od1688" => AlibabaDistributionOrderDetail1688::tableName()], "od1688.order_detail_id=od.id");
            $query->innerJoin(["g" => AlibabaDistributionGoodsList::tableName()], "g.id=od.goods_id");
            $query->andWhere(["od.order_id" => $order->id]);
            $selects = ["od.id", "od.order_id", "od.num", "od.unit_price", "od.total_original_price", "od.total_price", "od.is_refund", "od.refund_status", "od.shopping_voucher_decode_price",
                "od.shopping_voucher_num", "od.sku_labels", "od1688.app_id", "od1688.ali_order_id",  "g.cover_url", "g.name", "g.id as goods_id", "od1688.id as od1688_id"
            ];
            $orderDetails = $query->select($selects)->asArray()->all();
            if(!$orderDetails){
                throw new \Exception('[AlibabaDistributionOrderDetail] 详情记录不存在');
            }

            $apps = [];
            foreach ($orderDetails as &$orderDetail) {
                if(!isset($apps[$orderDetail['app_id']])){
                    $apps[$orderDetail['app_id']] = AlibabaApp::findOne($orderDetail['app_id']);
                }

                $app = $apps[$orderDetail['app_id']];

                //获取1688状态信息
                $distribution = new Distribution($app->app_key, $app->secret);
                $extraInfo = $this->get1688ExtraInfo($distribution, $app->access_token, $orderDetail['ali_order_id']);

                $orderDetail['ali_info']   = $extraInfo;
                $orderDetail['sku_labels'] = $orderDetail['sku_labels'] ? @json_decode($orderDetail['sku_labels'], true) : [];
                $orderDetail['sku_labels'] = $orderDetail['sku_labels'] ? implode(",", $orderDetail['sku_labels']) : "";
            }
            
            $orderData = $order->getAttributes();
            $orderData['details']    = $orderDetails;
            $orderData['created_at'] = date('Y-m-d H:i:s', $order['created_at']);
            $orderData['pay_at']     = $order->is_pay ? date('Y-m-d H:i:s', $order->pay_at) : "";
            $orderData['shopping_voucher_total_use_num'] = floatval($orderData['shopping_voucher_express_use_num']) + floatval($orderData['shopping_voucher_use_num']);

            return $this->returnApiResultData(ApiCode::CODE_SUCCESS,'', $orderData);
        } catch (\Exception $e) {
            return $this->returnApiResultData(ApiCode::CODE_FAIL,CommonLogic::getExceptionMessage($e));
        }
    }

    /**
     * 获取1688信息
     * @param Distribution $distribution
     * @param $token
     * @param $ali_order_id
     * @return array
     * @throws \Exception
     */
    private function get1688ExtraInfo(Distribution $distribution, $token, $ali_order_id){
        $res = $distribution->requestWithToken(new GetOrderInfo([
            "webSite" => "1688",
            "orderId" => $ali_order_id
        ]), $token);
        if(!empty($res->error)){
            throw new \Exception($res->error);
        }
        if(!$res instanceof GetOrderInfoResponse){
            throw new \Exception("[GetOrderInfoResponse]返回结果异常");
        }
        $info['status'] = isset($res->result['baseInfo']) ? $res->result['baseInfo']['status'] : "-1";
        $allStatusTexts = [
            'waitbuyerpay'     => '未付款',
            'waitsellersend'   => '待发货',
            'waitbuyerreceive' => '待收货',
            'confirm_goods'    => '已收货',
            'success'          => '交易成功',
            'cancel'           => '已取消',
            'terminated'       => '交易终止'
        ];
        $info['status_text'] = !empty($info['status']) && isset($allStatusTexts[$info['status']]) ? $allStatusTexts[$info['status']] : "未知错误";

        return $info;
    }
}