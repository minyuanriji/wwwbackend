<?php

namespace app\plugins\addcredit\plateform\sdk\k_default;

use app\plugins\addcredit\forms\common\Request;
use app\plugins\addcredit\plateform\result\SubmitResult;
use app\plugins\addcredit\forms\common\TelType;
use yii\base\BaseObject;

class SubmitOrderAction extends BaseObject
{
    public $AddcreditOrder;
    public $AddcreditPlateforms;

    const TEL_TYPE = [0,1,2];//手机号码类型  0 移动 1 联通 2 电信

    /**
     * 第三方需要参数
     *  mchid	String(16)	是	客户ID(需联系商务生成)
        tel	    String(32)	是	充值号码
        orderid	String(32)	是	充值订单号
        price	Integer	    是	充值金额
        teltype	String	    是	运营商 0 移动 1 联通 2 电信
        timeout	Integer	    是	到账范围值 600-86400 秒
        notify	String	    是	异步回调地址（POST）
        time	String	    是	时间戳
        rand	String	    是	随机数 100000-999999
        sign	String	    是	MD5 签名（参考示例）md5(mchid+tel+price+orderid+teltype+timeout+notify+time+rand+ 商 户 秘 钥 )
     *
     * */
    public function run ()
    {
        $SubmitResult = new SubmitResult();
        try {
            $plateforms_param = @json_decode($this->AddcreditPlateforms->json_param);
            $teltype = (new TelType())->getPhoneType($this->AddcreditOrder->mobile);
            if (!in_array($teltype, self::TEL_TYPE)) throw new \Exception("手机号码错误");

            $timeStamp = time();
            $rand = rand(100000, 999999);
            $post_param = [
                'mchid'       => $plateforms_param->id,
                'tel'         => $this->AddcreditOrder->mobile,
                'orderid'     => $this->AddcreditOrder->order_no,
                'price'       => (int)$this->AddcreditOrder->order_price,
                'teltype'     => $teltype,
                'timeout'     => 1000,
                'notify'      => 1,
                'time'        => $timeStamp,
                'rand'        => $rand,
                'sign'        => md5($plateforms_param->id . $this->AddcreditOrder->mobile . $this->AddcreditOrder->order_price . $this->AddcreditOrder->order_no . $teltype . 1000 . 1 . $timeStamp . $rand . $plateforms_param->secret_key)
            ];
            $response = Request::execute(Config::PHONE_BILL_SUBMIT, $post_param);
            $parseArray = @json_decode($response, true);
            if (!isset($parseArray['code'])) {
                throw new \Exception("解析数据错误");
            }
            if ($parseArray['code'] != Code::ORDER_SUCCESS) {
                if (isset($parseArray['code'])) {
                    throw new \Exception(Msg::submitMsg()[$parseArray['code']]);
                } else {
                    throw new \Exception("未知错误 " . $parseArray['code']);
                }
            }
            $SubmitResult->code = $parseArray['code'];
            $SubmitResult->response_content = $response;
            $SubmitResult->request_data = json_encode($post_param);
            $SubmitResult->message = $parseArray['msg'];
        } catch (\Exception $e) {
            $SubmitResult->code = SubmitResult::CODE_FAIL;
            $SubmitResult->message = $e->getMessage();
        }
        return $SubmitResult;
    }
}