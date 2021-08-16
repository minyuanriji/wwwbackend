<?php

namespace app\plugins\addcredit\plateform\sdk\two_sdk;

use app\core\ApiCode;
use app\plugins\addcredit\forms\common\Request;
use app\plugins\addcredit\plateform\result\SubmitResult;
use app\plugins\addcredit\forms\common\TelType;
use yii\base\BaseObject;

class SubmitOrderAction extends BaseObject
{
    public $AddcreditOrder;
    public $AddcreditPlateforms;

    /**
     * 第三方需要参数
     *  mch_id	        String(16)	是	商户ID
        platform_no	    int	        是	所属平台    所属的供应商平台，如：20000 (话单)
        no	            String(32)	是	订单号码    用于关联话单订单号
        args	        string	    是	手机号码    待充值的手机号码
        amount	        float	    是   充值金额    充值金额
        notify_url	    String	    是	通知地址    通知地址，用于订单付款后通知，服务器将 post 请求这个地址，将会携带参数如下： mch_id (商户ID) platform_no (平台编号) no (订单号) amount (金额) ts (时间戳) status（订单状态）-8 待获取， 0 待付款 1 待入账 -1 已超时 2 付款中 3 已入账 4 已退款 -5 已失败 -6 释放中 sign (签名) args (附加参数) sign = md5(mch_id + platform_no + no + amount + ts + status + 商户密钥).toUpperCase()(统一转大写) 回调成功请返回字符串：success
        sign	        String	    是	签名      sign = md5(mch_id + platform_no + amount + no + notify_url + 商户密钥) (全部转大写)
    返回结果：字段名	    变量名	    必填	    类型	        示例值	    描述
            code	    返回状态码	true	int	        0	        非0表示错误
            message	    错误码消息	true	string	    Success	    返回数据的消息
            data	    返回数据	    true	int	        object	    返回添加成功数据模型
     * */
    public function run ()
    {
        $SubmitResult = new SubmitResult();
        try {
            $plateforms_param = json_decode($this->AddcreditPlateforms->json_param,true);
//            $teltype = (new TelType())->getPhoneType($this->AddcreditOrder->mobile);
            $post_param = [
                'mch_id'        => $plateforms_param['id'],
                'platform_no'   => '20000',
                'no'            => $this->AddcreditOrder->order_no,
                'args'          => $this->AddcreditOrder->mobile,
                'amount'        => $this->AddcreditOrder->order_price,
                'notify_url'    => '',//固定值
                'sign'          => strtoupper(md5($plateforms_param['id'] . '20000' . $this->AddcreditOrder->order_price . $this->AddcreditOrder->order_no . '' . $plateforms_param['secret_key'])),//固定值
            ];
            $response = Request::execute(Config::PHONE_BILL_SUBMIT, $post_param);
            print_r($response);die;
            $parseArray = @json_decode($response, true);
            if (!isset($parseArray['code'])) {
                throw new \Exception("解析数据错误", ApiCode::CODE_FAIL);
            }

            if ($parseArray['code'] != Code::ORDER_SUCCESS) {
                if (isset($parseArray['message'])) {
                    throw new \Exception(Msg::submitMsg()[$parseArray['code']], ApiCode::CODE_FAIL);
                } else {
                    throw new \Exception("未知错误 " . $parseArray['code'], ApiCode::CODE_FAIL);
                }
            }
            $SubmitResult->code = $parseArray['code'];
            $SubmitResult->response_content = $response;
            $SubmitResult->request_data = json_encode($post_param);
            $SubmitResult->message = $parseArray['message'];
        } catch (\Exception $e) {
            $SubmitResult->code = SubmitResult::CODE_FAIL;
            $SubmitResult->message = $e->getMessage();
        }
        return $SubmitResult;
    }
}