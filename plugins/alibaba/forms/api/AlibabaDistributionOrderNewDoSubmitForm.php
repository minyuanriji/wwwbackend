<?php

namespace app\plugins\alibaba\forms\api;

use app\core\ApiCode;
use app\models\User;
use app\plugins\alibaba\helpers\AliGoodsHelper;
use app\plugins\alibaba\models\AlibabaApp;
use app\plugins\alibaba\models\AlibabaDistributionGoodsList;
use app\plugins\alibaba\models\AlibabaDistributionOrder;
use app\plugins\alibaba\models\AlibabaDistributionOrderDetail;
use app\plugins\shopping_voucher\forms\common\ShoppingVoucherLogModifiyForm;
use lin010\alibaba\c2b2b\AliDistributionException;
use lin010\alibaba\c2b2b\api\GetAddress;
use lin010\alibaba\c2b2b\api\GetAddressResponse;
use lin010\alibaba\c2b2b\api\OrderGetPreview;
use lin010\alibaba\c2b2b\api\OrderGetPreviewResponse;
use lin010\alibaba\c2b2b\Distribution;

class AlibabaDistributionOrderNewDoSubmitForm extends AlibabaDistributionOrderNewForm {

    public function rules(){
        return array_merge(parent::rules(), [
            [["address"], "required"]
        ]);
    }

    public function submit(){
        if(!$this->validate()){
            return $this->responseErrorInfo();
        }

        $t = \Yii::$app->db->beginTransaction();
        try {

            $data = $this->getData();

            if (!$data['user_address'] || empty($data['user_address']['mobile'])) {
                throw new \Exception("未选择收货地址");
            }

            $user = User::findOne(\Yii::$app->user->id);

            foreach ($data['list'] as $orderItem) {
                $order = new AlibabaDistributionOrder();
                $order->mall_id = \Yii::$app->mall->id;
                $order->user_id = $user->getId();
                $order->order_no = static::getOrderNo('S');
                $order->total_price = $orderItem['total_price'];
                $order->total_pay_price = $orderItem['total_price'];
                $order->total_goods_price = $orderItem['total_price'] - $orderItem['express_price'];
                $order->express_original_price = $orderItem['express_price'];
                $order->express_price = $orderItem['express_price'];
                $order->total_goods_original_price = $orderItem['total_goods_original_price'];

                //购物券抵扣
                $order->shopping_voucher_use_num = $orderItem['shopping_voucher_use_num'];
                $order->shopping_voucher_decode_price = $orderItem['shopping_voucher_decode_price'];
                $order->shopping_voucher_express_use_num = $orderItem['shopping_voucher_express_use_num'];
                $order->shopping_voucher_express_decode_price = $orderItem['shopping_voucher_express_decode_price'];

                $order->ali_address_info = "{}";
                $order->name = !empty($data['user_address']['name']) ? $data['user_address']['name'] : "";
                $order->mobile = !empty($data['user_address']['mobile']) ? $data['user_address']['mobile'] : "";
                $order->address = $data['user_address']['detail'];
                $order->address_id  = 0;
                $order->province_id = 0;
                $order->remark = $this->remark;
                $order->token = \Yii::$app->security->generateRandomString();
                $order->is_pay = 0;
                $order->pay_type = 0;

                if (!$order->save()) {
                    throw new \Exception($this->responseErrorMsg($order));
                }

                //生成订单详情
                foreach ($orderItem['goods_list'] as $goodsItem) {
                    $this->extraOrderDetail($order, $goodsItem);
                }

                //扣除购物券
                $shoppingVoucherUseNum = $order->shopping_voucher_express_use_num + $order->shopping_voucher_use_num;
                if ($shoppingVoucherUseNum > 0) {
                    $modifyForm = new ShoppingVoucherLogModifiyForm([
                        "money" => $shoppingVoucherUseNum,
                        "desc" => "订单(" . $order->id . ")创建扣除购物券：" . $shoppingVoucherUseNum,
                        "source_id" => $order->id,
                        "source_type" => "target_alibaba_distribution_order"
                    ]);
                    $modifyForm->sub($user);
                }
            }

            $t->commit();

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'data' => [
                    'token' => $order->token
                ]
            ];
        }catch (AliDistributionException $e){
            $t->rollBack();

            //设置异常告警
            $aliGoods = AlibabaDistributionGoodsList::findOne($goodsItem['id']);
            AliGoodsHelper::setWarn($aliGoods, $goodsItem['sku_id'], $e->getMessage());

            return [
                'code' => ApiCode::CODE_FAIL,
                'msg'  => $e->getMessage(),
                'error' => [
                    'file' => $e->getFile(),
                    'line' => $e->getLine()
                ]
            ];
        }catch (\Exception $e){
            $t->rollBack();
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg'  => $e->getMessage(),
                'error' => [
                    'file' => $e->getFile(),
                    'line' => $e->getLine()
                ]
            ];
        }
    }

    /**
     * 订单扩展
     * @param AlibabaDistributionOrder $order
     * @param $goodsItem
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function extraOrderDetail(AlibabaDistributionOrder $order, $goodsItem){

        //检查阿里巴巴商品是否可以下单
        $this->validateAliGoods($order, $goodsItem);

        $orderDetail                        = new AlibabaDistributionOrderDetail();
        $orderDetail->mall_id               = $order->mall_id;
        $orderDetail->app_id                = $goodsItem['app_id'];
        $orderDetail->order_id              = $order->id;
        $orderDetail->goods_id              = $goodsItem['id'];
        $orderDetail->ali_spec_id           = $goodsItem['ali_spec_id'];
        $orderDetail->sku_id                = $goodsItem['sku_id'];
        $orderDetail->ali_sku               = $goodsItem['ali_sku'];
        $orderDetail->sku_labels            = json_encode($goodsItem['sku_labels']);
        $orderDetail->num                   = $goodsItem['num'];
        $orderDetail->ali_num               = $goodsItem['ali_num'] * $goodsItem['num'];
        $orderDetail->unit_price            = $goodsItem['price'];
        $orderDetail->total_original_price  = $goodsItem['total_original_price'];
        $orderDetail->total_price           = $goodsItem['total_price'];

        //购物券抵扣
        if(isset($goodsItem['use_shopping_voucher_decode_price'])){
            $orderDetail->shopping_voucher_decode_price = $goodsItem['use_shopping_voucher_decode_price'];
        }
        if(isset($goodsItem['use_shopping_voucher_num'])){
            $orderDetail->shopping_voucher_num = $goodsItem['use_shopping_voucher_num'];
        }
        if(!$orderDetail->save()){
            throw new \Exception($this->responseErrorMsg($orderDetail));
        }
    }

    /**
     * 检查阿里巴巴商品是否能下单
     * @param AlibabaDistributionOrder $order
     * @param $goodsItem
     * @throws \Exception
     */
    private function validateAliGoods(AlibabaDistributionOrder $order, $goodsItem){

        try {
            $app = AlibabaApp::findOne($goodsItem['app_id']);
            $distribution = new Distribution($app->app_key, $app->secret);

            //解析1688的地址
            $res = $distribution->requestWithToken(new GetAddress([
                "addressInfo" => "{$order->address}"
            ]), $app->access_token);
            if(!empty($res->error)){
                throw new AliDistributionException($res->error);
            }
            if(!$res instanceof GetAddressResponse){
                throw new AliDistributionException("[GetAddressResponse]返回结果异常");
            }

            $aliAddrInfo = (array)@json_decode($res->result, true);

            $res = $distribution->requestWithToken(new OrderGetPreview([
                "addressParam" => json_encode([
                    "fullName"     => $order->name,
                    "mobile"       => $order->mobile,
                    "phone"        => $order->mobile,
                    "postCode"     => isset($aliAddrInfo['postCode']) ? $aliAddrInfo['postCode'] : "",
                    "cityText"     => "",
                    "provinceText" => "",
                    "areaText"     => "",
                    "address"      => $order->address,
                    "districtCode" => isset($aliAddrInfo['addressCode']) ? $aliAddrInfo['addressCode'] : ""
                ]),
                "cargoParamList" => json_encode([
                    'offerId'   => $goodsItem['ali_offerId'],
                    'specId'    => $goodsItem['ali_spec_id'],
                    'quantity'  => $goodsItem['num']
                ])
            ]), $app->access_token);
            if(!$res instanceof OrderGetPreviewResponse){
                throw new AliDistributionException("[OrderGetPreviewResponse]返回结果异常");
            }

            if(!empty($res->error)){
                throw new AliDistributionException($res->error);
            }
        }catch (AliDistributionException $e){
            throw $e;
        }
    }
}