<?php

namespace app\plugins\addcredit\plateform\sdk\qyj_sdk;

use app\core\ApiCode;
use app\plugins\addcredit\forms\common\AccessToken;
use app\plugins\addcredit\forms\common\Request;
use app\plugins\addcredit\plateform\result\SubmitResult;
use app\plugins\addcredit\forms\common\TelType;
use yii\base\BaseObject;

class SubmitOrderAction extends BaseObject
{
    public $qyj_order_num;
    public $AddcreditPlateforms;

    /**
     * 第三方需要参数
     *  out_trade_num	String	    是	商户订单号，由商户自己生成唯一单号
        product_id	    number	    是	产品ID（当使用Q币自定义面值时，此参数不填）
        account	        String	    是	充值的账号。充值手机号/电费户号/视频账号
        userid          number      是  代理商账号ID，通过客服获取
        notify_url	    String      是	回调地址，用于接收充值状态回调
        amount	        String	    是	q币自定义面值
        sign	        String	    是	签名      sign = md5(mch_id + platform_no + amount + no + notify_url + 商户密钥) (全部转大写)
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
            $plateforms_param = json_decode($this->AddcreditPlateforms->json_param,true);
//            $teltype = (new TelType())->getPhoneType($this->AddcreditOrder->mobile);
            $param = [
                'app_id'    => $plateforms_param['id'],
                'order_num' => $this->qyj_order_num,
                'sign'      => strtolower(md5($plateforms_param['id'] . "!@#" . $plateforms_param['secret_key'])),
            ];
            $cacheKey = md5($plateforms_param['id'] . $plateforms_param['secret_key']);
            $token = AccessToken::getAccessToken($cacheKey, $plateforms_param['id'], $plateforms_param['secret_key']);
            if (!$token || $token['code'] == ApiCode::CODE_FAIL) {
                throw new \Exception($token['msg'] ?? '获取access_token失败');
            }
            $response = Request::execute(Config::PHONE_BILL_SUBMIT, $param, $token);
            $parseArray = json_decode($response, true);
            if (!isset($parseArray['code'])) {
                throw new \Exception("解析数据错误", ApiCode::CODE_FAIL);
            }

            if ($parseArray['code'] != Code::OVERALL_SITUATION_SUCCESS) {
                throw new \Exception($parseArray['message']);
            }

            $SubmitResult->code = SubmitResult::CODE_SUCC;
            $SubmitResult->response_content = $response;
            $SubmitResult->request_data = json_encode($param);
            $SubmitResult->message = $parseArray['message'];
        } catch (\Exception $e) {
            $SubmitResult->code = SubmitResult::CODE_FAIL;
            $SubmitResult->message = $e->getMessage();
        }
        return $SubmitResult;
    }
}