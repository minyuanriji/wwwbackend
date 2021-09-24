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

            $detail1688 = AlibabaDistributionOrderDetail1688::findOne($this->order_id);
            if(!$detail1688){
                throw new \Exception('订单不存在');
            }

            $order = AlibabaDistributionOrder::findOne($detail1688->order_id);

            $data = $order->getAttributes();
            $data['detail'] = AlibabaDistributionOrderDetail::find()->where(['id' => $detail1688->order_detail_id, 'is_delete' => 0])->asArray()->all();
            $data['shopping_voucher_num'] = $data['shopping_voucher_express_use_num'];
            if ($data['detail']) {
                foreach ($data['detail'] as &$item) {
                    $goods = AlibabaDistributionGoodsList::findOne(['id' => $item['goods_id']]);
                    $item['name']       = $goods['name'];
                    $item['cover_url']  = $goods['cover_url'];
                    $item['sku_labels'] = json_decode($item['sku_labels'], true);
                    $data['shopping_voucher_num'] += $item['shopping_voucher_num'];
                }
            }
            $data['created_at'] = date('Y-m-d H:i:s', $order['created_at']);
            $data['pay_at']     = date('Y-m-d H:i:s', $order['pay_at']);

            $app = AlibabaApp::findOne($detail1688->app_id);
            $distribution = new Distribution($app->app_key, $app->secret);

            //获取1688信息
            $data = array_merge($data, $this->get1688ExtraInfo($distribution, $app->access_token, $detail1688));

            return $this->returnApiResultData(ApiCode::CODE_SUCCESS,'', $data);
        } catch (\Exception $e) {
            return $this->returnApiResultData(ApiCode::CODE_FAIL,CommonLogic::getExceptionMessage($e));
        }
    }

    /**
     * 获取1688信息
     * @param Distribution $distribution
     * @param $token
     * @param AlibabaDistributionOrderDetail1688 $detail1688
     * @return array
     * @throws \Exception
     */
    private function get1688ExtraInfo(Distribution $distribution, $token, AlibabaDistributionOrderDetail1688 $detail1688){
        $res = $distribution->requestWithToken(new GetOrderInfo([
            "webSite" => "1688",
            "orderId" => $detail1688->ali_order_id
        ]), $token);
        if(!empty($res->error)){
            throw new \Exception($res->error);
        }
        if(!$res instanceof GetOrderInfoResponse){
            throw new \Exception("[GetOrderInfoResponse]返回结果异常");
        }

        $info['status'] = $res->result['baseInfo']['status'];
        $allStatusTexts = [
            'waitbuyerpay'     => '未付款',
            'waitsellersend'   => '待发货',
            'waitbuyerreceive' => '待收货',
            'confirm_goods'    => '已收货',
            'success'          => '交易成功',
            'cancel'           => '已取消',
            'terminated'       => '交易终止'
        ];
        $info['status_text'] = !empty($data['status']) && isset($allStatusTexts[$data['status']]) ? $allStatusTexts[$data['status']] : "未知错误";

        return $info;
    }
}