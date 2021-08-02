<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 快递
 * Author: zal
 * Date: 2020-06-12
 * Time: 10:57
 */


namespace app\forms\api\express;

use app\core\ApiCode;
use app\models\BaseModel;
use app\models\Mall;
use app\models\Order;
use app\models\OrderDetailExpress;
use app\models\Express;
use Flex\Express\ExpressBird;


class ExpressForm extends BaseModel
{
    public $mobile; // 手机号，用于顺丰查信息
    public $express;
    public $express_no;
    public $express_code;//物流编码
    public $customer_name;//京东物流特殊要求字段，商家编码

    public function rules()
    {
        return [
            [['customer_name', 'mobile', 'express'], 'string'],
            [['express_no', 'express_code'], 'required'],
        ];
    }

    public function search()
    {
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }
//        if ($this->customer_name === 'undefined') $this->customer_name = null;
        try {
//            if (substr_count($this->express, '京东') && empty($this->customer_name)) {
//                throw new \Exception('京东物流必须填写京东商家编码');
//            }
            $expressData = $this->getExpressData();
//            $expressData = $this->getNTxExpressData();
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => 'success',
                'data' => [
                    'express' => $expressData,
                    'order' => [
                        'express' => $this->express,
                        'express_no' => $this->express_no,
                    ],
                ]
            ];
        } catch (\Exception $exception) {
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => $exception->getMessage(),
                'data' => [
                    'express' => null,
                    'order' => [
                        'express' => $this->express,
                        'express_no' => $this->express_no,
                    ],
                ]
            ];
        }
    }

    private function getKuaidiBirdConfig()
    {
        try {
            $mall = (new Mall())->getMallSetting(['express_aliapy_code', 'kdniao_mch_id', 'kdniao_api_key']);
        } catch (\Exception $e) {
        }
        if (!$mall || !$mall['kdniao_mch_id'] || !$mall['kdniao_api_key'] || !$mall['express_aliapy_code']) {
            return ['', ''];
        }
        $express_aliapy_code = $mall['express_aliapy_code'];
        $mch_id = $mall['kdniao_mch_id'];
        $api_key = $mall['kdniao_api_key'];
        return [$express_aliapy_code, $mch_id, $api_key];
    }

    //阿里云暂时不用
    private function getExpressData()
    {
        $statusMap = [
            // -1 => '已揽件',
            // 0 => '已揽件',
            // 1 => '已发出',
            // 2 => '在途中',
            // 3 => '已签收',
            // 4 => '问题件',
            -1 => '单号或快递公司代码错误', 
            0 => '暂无轨迹',
            1 => '快递收件',
            2 => '在途中',
            3 => '签收',
            4 => '问题件',
            5 => '疑难件',
            6 => '退件签收',
        ];
        $status = null;
        list($expressAliapyCode, $EBusinessID, $AppKey) = $this->getKuaidiBirdConfig();
        $classArgs['EBusinessID'] = $EBusinessID;
        $classArgs['AppKey'] = $AppKey;
        $list = $this->getExpressAli($expressAliapyCode,$this->express_code,$this->express_no);
        // $express = new ExpressBird($EBusinessID, $AppKey);
        // $list = $express->track($this->express_no, $this->express_code, '', $this->customer_name);
        $list = (json_decode($list, true));
        if ($list) {
            $status = $statusMap[$list['State']];
        }
        return [
            'status' => $status,
            'list' => $list,
        ];
    }


    private function getExpressAli($app_code,$shipping_code,$express_code){
        if(!$app_code){
            throw new \Exception('缺少主要参数');
        }else if(!$shipping_code && !$express_code){
            throw new \Exception('缺少快递单号或快递公司代码');
        }
        $host = "http://wdexpress.market.alicloudapi.com";//api访问链接
        $path = "/gxali";//API访问后缀
        $method = "GET";
        $headers = array();
        array_push($headers, "Authorization:APPCODE " . $app_code);
        if ($shipping_code == 'SF') {
            $querys = "n=".$express_code.":".$this->getMobileLast4Num()."&t=".$shipping_code."";  //参数写在这里
        } else {
            $querys = "n=".$express_code."&t=".$shipping_code."";  //参数写在这里
        }
        $bodys = "";
        $url = $host . $path . "?" . $querys;//url拼接
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_FAILONERROR, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HEADER, false);
        if (1 == strpos("$".$host, "https://"))
        {
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        }
        return curl_exec($curl);
    }

    //腾讯物流
    private function getNTxExpressData()
    {
        $statusMap = [
            -1 => '待查询',
            0 => '查询异常',
            1 => '暂无记录',
            2 => '在途中',
            3 => '派送中',
            4 => '已签收',
            5 => '用户拒签',
            6 => '疑难件',
            7 => '无效单',
            8 => '超时单',
            9 => '签收失败',
            10 => '退回',
        ];
        $status = null;
        list($secretId, $secretKey,$source) = ['AKIDkHhRcy7odBqR2Y6m403Ic3YPfb378Fmu6sy','ko8OOwY1Jz2NO4x0DAwqJZCMGx8RLJ18WNhQph04','market'];
        $list = $this->getExpressTx($secretId,$secretKey,$source,'shunfeng',$this->express_no);//$this->express_code
        $list = (json_decode($list, true));
        if ($list) {
            $status = $statusMap[$list['State']];
        }
        return [
            'status' => $status,
            'list' => $list,
        ];
    }

    private function getExpressTx($secretId,$secretKey,$source,$shipping_code,$express_code)
    {
        if(!$secretId || !$secretKey){
            throw new \Exception('缺少主要参数');
        }else if(!$shipping_code && !$express_code){
            throw new \Exception('缺少快递单号或快递公司代码');
        }

        // 签名
        $datetime = gmdate('D, d M Y H:i:s T');
        $signStr = sprintf("x-date: %s\nx-source: %s", $datetime, $source);
        $sign = base64_encode(hash_hmac('sha1', $signStr, $secretKey, true));
        $auth = sprintf('hmac id="%s", algorithm="hmac-sha1", headers="x-date x-source", signature="%s"', $secretId, $sign);

        // 请求方法
        $method = 'GET';
        // 请求头
        $headers = array(
            'X-Source' => $source,
            'X-Date' => $datetime,
            'Authorization' => $auth,
        );
        // 查询参数
        $queryParams = array (
            'com' => $shipping_code,
            'nu' => $express_code,
            'phone' => '8185',//$this->getMobileLast4Num(),
        );
        // url参数拼接
        $url = 'https://service-6t1c9ush-1255468759.ap-shanghai.apigateway.myqcloud.com/release/point-list';
        if (count($queryParams) > 0) {
            $url .= '?' . http_build_query($queryParams);
        }
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array_map(function ($v, $k) {
            return $k . ': ' . $v;
        }, array_values($headers), array_keys($headers)));
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

        $data = curl_exec($ch);
        if (curl_errno($ch)) {
            echo "Error: " . curl_error($ch);
        } else {
            print_r($data);
        }
        curl_close($ch);die;
    }

    /**
     * 获取订单收件人手机号最后4位
     * @return mixed|string|null
     */
    private function getMobileLast4Num()
    {
        if ($this->mobile) {
            $mobile = $this->mobile;
        } else {
            $order = null;
            $orderDetailExpress = OrderDetailExpress::find()->where([
                'express' => $this->express,
                'express_no' => $this->express_no,
            ])->orderBy('id DESC')->one();
            if ($orderDetailExpress) {
                $order = Order::findOne($orderDetailExpress->order_id);
            } else {
                $order = Order::find()
                    ->where([
                        'express' => $this->express,
                        'express_no' => $this->express_no,
                    ])->one();
            }
            $mobile = $order ? $order->mobile : null;
        }
        if (!$mobile || mb_strlen($mobile) < 4) {
            return '';
        }
        return mb_substr($mobile, 0 - 4);
    }
}
