<?php

namespace app\plugins\alibaba\forms\mall;

use app\core\ApiCode;
use app\models\BaseModel;
use app\plugins\alibaba\models\AlibabaApp;
use app\plugins\alibaba\models\AlibabaDistributionOrderDetail1688;
use app\plugins\alibaba\models\AlibabaDistributionOrderRefund;
use lin010\alibaba\c2b2b\api\GetOrderInfoResponse;
use lin010\alibaba\c2b2b\Distribution;
use lin010\alibaba\c2b2b\api\GetOrderInfo;
use yii\base\BaseObject;

class AlibabaDistributionPaymentInfoForm extends BaseModel
{
    public $order_id;
    public $order_detail_id;

    public function rules()
    {
        return [
            [['order_id', 'order_detail_id'], 'required'],
            [['order_id', 'order_detail_id'], 'integer'],
        ];
    }

    public function getInfo()
    {

        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }

        try {
            $detail1688 = AlibabaDistributionOrderDetail1688::findOne(["order_detail_id" => $this->order_detail_id]);
            if(!$detail1688){
                throw new \Exception("[AlibabaDistributionOrderDetail1688] 1688订单信息不存在");
            }
            //获取1688该订单信息
            $app = AlibabaApp::findOne($detail1688->app_id);
            $distribution = new Distribution($app->app_key, $app->secret);
            $res = $distribution->requestWithToken(new GetOrderInfo([
                "webSite" => "1688",
                "orderId" => $detail1688->ali_order_id
            ]), $app->access_token);
            if(!$res instanceof GetOrderInfoResponse){
                throw new \Exception("[GetOrderInfoResponse]返回结果异常");
            }
            if($res->error){
                throw new \Exception($res->error);
            }

            $refund = AlibabaDistributionOrderRefund::findAll(['order_id' => $this->order_id, 'order_detail_id' => $this->order_detail_id]);
            if ($refund) {
                foreach ($refund as $item) {
                    if ($item['refund_type'] == 'shopping_voucher') {
                        $shoppingVoucherRefund = $item;
                    }
                    if ($item['refund_type'] == 'money') {
                        $moneyRefund = $item;
                    }
                }
            }
            return $this->returnApiResultData(ApiCode::CODE_SUCCESS, '打款成功', [
                'orderStatus' => $res->result['baseInfo']['status'],
                'refundStatus' => $res->result['baseInfo']['refundStatus'] ?? '',
                'moneyRefund' => $moneyRefund ?? '',
                'shoppingVoucherRefund' => $shoppingVoucherRefund ?? '',
            ]);
        } catch (\Exception $e) {
            return $this->returnApiResultData(ApiCode::CODE_FAIL, $e->getMessage());
        }
    }
}