<?php

namespace app\plugins\addcredit\plateform\sdk\k_default;

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
     *szAgentId	String(16)	是	客户ID(需联系商务生成)
        szOrderId	String(32)	是	商户平台自行生成的订单编号，保证唯一性，订单号大小写不敏感(由字母或数字组成)
        szPhoneNum	String(32)	是	充值号码、QQ号、
        加油卡号等
        nMoney	Integer	是	充值金额
        话费产品单位为：元
        流量产品单位为：M
        视频会员：填写产品原价
        nSortType	Integer	是	运营商编码：
        见附录2.1
        nProductClass	Integer	是	固定值：1
        nProductType	String	是	固定值：1
        szProductId	String	否	产品编码
        常规话费不需要填写（如需指定产品编码需与商务沟通确认）
        特定产品必填如流量产品
        szTimeStamp	DateTime	是	时间戳。
        用于判断过期响应，与接口服务器时间间隔1分钟内有效。
        格式:
        yyyy-MM-dd HH:mm:ss
        (例如：2016-01-01 07:23:00)
        szVerifyString	String	是	签名
        szNotifyUrl	String	是	完成结果回调通知地址（不参与签名）
        szFormat	String	否	结果返回格式:
        JSON（默认）
        XML
        thirdphone	String	否	非话费流量充值用户手机号,中石油必传,QQ会员提供QQ号
        params	String	否	json字符串，特定产品必传，如Q币订单需传ip，中石油必传姓名cardName、身份证号码cardId，账号类型numtype(手机号(默认可不传)：phone;QQ号码：qq,微信号码：wx)例：
        {"cardName":"张三","cardId":"xxxxx","numtype":"phone"}
     *
     * */
    public function run ()
    {
        $SubmitResult = new SubmitResult();
        try {
            $plateforms_param = @json_decode($this->AddcreditPlateforms->json_param);
            $teltype = (new TelType())->getPhoneType($this->AddcreditOrder->mobile);
            $timeStamp = date("Y-m-d H:i:s", time());
            $post_param = [
                'szAgentId'         => $plateforms_param->id,
                'szOrderId'         => $this->AddcreditOrder->order_no,
                'szPhoneNum'        => $this->AddcreditOrder->mobile,
                'nMoney'            => (int)$this->AddcreditOrder->order_price,
                'nSortType'         => $teltype,
                'nProductClass'     => 1,//固定值
                'nProductType'      => 1,//固定值
                'szTimeStamp'       => $timeStamp,
                'szVerifyString'    => md5('szAgentId=' . $plateforms_param->id . '&szOrderId=' . $this->AddcreditOrder->order_no . '&szPhoneNum=' . $this->AddcreditOrder->mobile . '&nMoney=' . (int)$this->AddcreditOrder->order_price . '&nSortType=' . $teltype . '&nProductClass=1&nProductType=1&szTimeStamp=' . $timeStamp . '&szKey=' . $plateforms_param->secret_key),
            ];
            $str_params = '';
            foreach ($post_param as $key => $value) {
                if ($key == 'szAgentId') {
                    $str_params .= "?$key=".$value;
                } else {
                    $str_params .= "&$key=".$value;
                }
            }
            $response = Request::http_get(Config::PHONE_BILL_SUBMIT . $str_params);
//            $response = Request::execute(Config::PHONE_BILL_SUBMIT, $post_param);
            $parseArray = @json_decode($response, true);
            if (!isset($parseArray['nRtn'])) {
                throw new \Exception("解析数据错误", ApiCode::CODE_FAIL);
            }

            if ($parseArray['nRtn'] != Code::ORDER_SUCCESS) {
                if (isset($parseArray['szRtnCode'])) {
                    throw new \Exception($parseArray['szRtnCode'] . " " . $parseArray['nRtn'], ApiCode::CODE_FAIL);
                } else {
                    throw new \Exception("未知错误 " . $parseArray['nRtn'], ApiCode::CODE_FAIL);
                }
            }

            $SubmitResult->code = $parseArray['nRtn'];
            $SubmitResult->response_content = $response;
            $SubmitResult->request_data = json_encode($post_param);
            $SubmitResult->message = $parseArray['szRtnCode'];

        } catch (\Exception $e) {
            $SubmitResult->code = SubmitResult::CODE_FAIL;
            $SubmitResult->message = $e->getMessage();
        }

        return $SubmitResult;
    }


}