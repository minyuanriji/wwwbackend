<?php

namespace app\plugins\addcredit\plateform\sdk\kcb_sdk;

use app\core\ApiCode;
use app\plugins\addcredit\forms\common\Request;
use app\plugins\addcredit\plateform\result\SubmitResult;
use yii\base\BaseObject;

class SubmitOrderAction extends BaseObject
{
    public $AddcreditOrder;
    public $AddcreditPlateforms;

    /**
     * 第三方需要参数
     *  out_trade_num	String	    是	商户订单号，由商户自己生成唯一单号
        product_id	    number	    是	产品ID（当使用Q币自定义面值时，此参数不填）
        account	        String	    是	充值的账号。充值手机号/电费户号/视频账号
        userid          number      是  代理商账号ID，通过客服获取
        notify_url	    String      是	回调地址，用于接收充值状态回调
        amount	        String	    是	q币自定义面值
        sign	        String	    是	签名
     *
    返回结果：字段名	    变量名	    必填	    描述
            errno       string      true    错误码，0代表成功，非0代表提交失败
            errmsg      string      true    错误描述
            data        object      true    errno=0时 返回数据
                order_number    string true 系统定单号
                mobile          string true 充值手机号
                product_id      string true 产品ID
                total_price     string true 消费金额
                out_trade_num   string true 外部订单号
                title           string true 充值产品说明
     * */
    public function run ()
    {
        $SubmitResult = new SubmitResult();
        try {
            $configs = Helpers::getPlateConfig($this->AddcreditPlateforms->json_param);
            $orderNo = $this->AddcreditOrder->order_no;
            $param = [
                'out_trade_num'    => $orderNo,
                'product_id'       => $this->AddcreditOrder->product_id,
                'account'          => $this->AddcreditOrder->mobile,
                'userid'           => $configs['app_id'],
                'notify_url'       => "https://www.mingyuanriji.cn/web/pay-notify/telephone.php",
                'amount'           => 0,
            ];
            ksort($param);
            $param_str = '';
            foreach ($param as $key => $item) {
                $param_str .= $key . '=' . $item . '&';
            }
            $sign_str = $param_str . 'apikey=' . $configs['secret_key'];
            $sign = strtoupper(md5($sign_str));
            $param_str .= "&sign=" . $sign;
            $response = Request::http_get(Config::PHONE_BILL_SUBMIT . '?' . $param_str);

            $SubmitResult->request_data = json_encode($param);

            $parseArray = json_decode($response, true);
            if (!isset($parseArray['errno'])) {
                throw new \Exception("解析数据错误", ApiCode::CODE_FAIL);
            }

            if ($parseArray['errno'] != Code::ORDER_SUCCESS) {
                if (isset($parseArray['errmsg'])) {
                    throw new \Exception($parseArray['errmsg']);
                } else {
                    throw new \Exception("未知错误 " . $parseArray['errno']);
                }
            }

            $SubmitResult->code = $parseArray['errno'];
            $SubmitResult->response_content = $response;
            $SubmitResult->message = $parseArray['errmsg'];
        } catch (\Exception $e) {
            $SubmitResult->code = SubmitResult::CODE_FAIL;
            $SubmitResult->message = $e->getMessage();
        }
        return $SubmitResult;
    }

    private function getNotifyUrl($file)
    {
        $protocol = env('PAY_NOTIFY_PROTOCOL');
        $url = \Yii::$app->request->hostInfo . \Yii::$app->request->baseUrl . '/pay-notify/' . $file;
        if ($protocol) {
            $url = str_replace('http://', ($protocol . '://'), $url);
            $url = str_replace('https://', ($protocol . '://'), $url);
        }
        return $url;
    }
}