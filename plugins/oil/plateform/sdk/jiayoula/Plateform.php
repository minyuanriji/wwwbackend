<?php

namespace app\plugins\oil\plateform\sdk\jiayoula;

use app\plugins\oil\models\OilOrders;
use app\plugins\oil\models\OilProduct;
use app\plugins\oil\plateform\BasePlateform;

class Plateform extends BasePlateform
{
    /**
     * 提交订单
     * @param OilOrders $order
     * @param OilProduct $product
     * @return bool|string|null
     */
    public function submit(OilOrders $order, OilProduct $product)
    {
        $config = $this->platModel->getParams();

        $appId = isset($config['appId']) ? $config['appId'] : "";
        $appSecret = isset($config['appSecret']) ? $config['appSecret'] : "";
        try {

            $params['appId']      = $appId;
            $params['orderNo']    = $order->order_no;
            $params['amount']     = floatval($product->price);
            $params['timestamp']  = time();
            $params['couponType'] = $order->province_id == 2088 ? 1 : 0; //券码类型，0-广东，1-广西【不参与签名】
            $signStr = "orderNo=".$params['orderNo']."&amount=".$params['amount']."&timestamp=".$params['timestamp']."&appSecret={$appSecret}";
            $params['signature'] = strtolower(md5($signStr));

            $url = "https://exchange-code-api.gzhyts.com/api/gdSinopecExchangeCode/getExchangeCode";
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
            curl_setopt($ch, CURLOPT_TIMEOUT, 15);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);

            $res = @curl_exec($ch);
            $errno  = curl_errno($ch);
            $error  = curl_error($ch);

            return $res;
        }catch (\Exception $e){
            return null;
        }

    }

    /**
     * 兑换接口
     * @param OilOrders $order
     * @param OilProduct $product
     */
    public function exchange(OilOrders $order, OilProduct $product){
        $config = $this->platModel->getParams();

        $appId = isset($config['appId']) ? $config['appId'] : "";
        $appSecret = isset($config['appSecret']) ? $config['appSecret'] : "";
        try {
            //兑换码获取
            $couponCode = "";
            $responseData = json_decode($order->plat_response_data, true);
            if(isset($responseData['data'])){
                $couponCode = isset($responseData['data']['couponCode']) ? $responseData['data']['couponCode'] : "";
            }


            $params['appId']      = $appId;
            $params['mobile']     = $order->mobile;
            $params['couponCode'] = $couponCode;
            $params['timestamp']  = time();

            $signStr = "mobile=".$params['mobile']."&couponCode={$couponCode}&timestamp=".$params['timestamp']."&appSecret={$appSecret}";
            $params['signature'] = strtolower(md5($signStr));

            $url = "https://exchange-code-api.gzhyts.com/api/gdSinopecExchangeCode/exchangeCode";
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
            curl_setopt($ch, CURLOPT_TIMEOUT, 15);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);

            $res = @curl_exec($ch);
            $errno  = curl_errno($ch);
            $error  = curl_error($ch);

            return $res;
        }catch (\Exception $e){
            return null;
        }
    }

}