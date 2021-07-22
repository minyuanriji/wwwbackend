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

    public function run ()
    {
        $SubmitResult = new SubmitResult();
        try {
            $plateforms_param = json_decode($this->AddcreditPlateforms->json_param);
            $teltype = (new TelType())->getPhoneType($this->AddcreditOrder->mobile);
            $timeout = 600;
            $rand = rand(0, 6);
            $post_param = [
                'mchid'     => $plateforms_param['mch_id'],
                'tel'       => $this->AddcreditOrder->mobile,
                'orderid'   => $this->AddcreditOrder->id,
                'price'     => (int)$this->AddcreditOrder->order_price,
                'teltype'   => $teltype,
                'timeout'   => $timeout,
                'notify'    => '',
                'time'      => time(),
                'rand'      => $rand,
                'sign'      => md5($plateforms_param['mch_id'] . $this->AddcreditOrder->mobile . (int)$this->AddcreditOrder->order_price . $this->AddcreditOrder->id . $teltype . $timeout . '' . time() . $rand . $plateforms_param[''])
            ];
            $response = Request::execute(Config::PHONE_BILL_SUBMIT, $post_param);
            $parseArray = @json_decode($response, true);
            if (!isset($parseArray['code'])) {
                throw new \Exception("解析数据错误", ApiCode::CODE_FAIL);
            }

            if ($parseArray['code'] != Code::ORDER_SUCCESS) {
                if (isset($parseArray['msg'])) {
                    throw new \Exception($parseArray['msg'] . " " . $parseArray['code'], ApiCode::CODE_FAIL);
                } else {
                    throw new \Exception("未知错误 " . $parseArray['code'], ApiCode::CODE_FAIL);
                }
            }

            $SubmitResult->code = $parseArray['code'];
            $SubmitResult->response_content = $response;
            $SubmitResult->request_data = json_encode($post_param);

        } catch (\Exception $e) {
            $SubmitResult->code = SubmitResult::CODE_FAIL;
            $SubmitResult->message = $e->getMessage();
        }

        return $SubmitResult;
    }


}