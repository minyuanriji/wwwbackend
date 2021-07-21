<?php

namespace app\plugins\addcredit\plateform\sdk\k_default;

use app\core\ApiCode;
use app\plugins\addcredit\forms\common\Request;
use app\plugins\addcredit\plateform\result\BalanceQueryResult;
use app\plugins\addcredit\plateform\result\SubmitResult;
use yii\base\BaseObject;

class AccountBalanceQueryAction extends BaseObject
{
    public $plateforms_params;

    public function run ()
    {
        $BalanceQueryResult = new BalanceQueryResult();
        try {
            $post_param = [
                'mchid'      => $this->plateforms_params['mch_id'],
                'sign'       => md5($this->plateforms_params['mch_id'] . $this->plateforms_params['my'])
            ];
            $response = Request::execute(Config::BALANCE_QUERY, $post_param);
            $parseArray = @json_decode($response, true);
            if (!isset($parseArray['code'])) {
                throw new \Exception("解析数据错误", ApiCode::CODE_FAIL);
            }

            if ($parseArray['code'] != Code::BALANCE_QUERY_SUCCESS) {
                if (isset($parseArray['msg'])) {
                    throw new \Exception($parseArray['msg'] . " " . $parseArray['code'], ApiCode::CODE_FAIL);
                } else {
                    throw new \Exception("未知错误 " . $parseArray['code'], ApiCode::CODE_FAIL);
                }
            }

            $BalanceQueryResult->code = $parseArray['code'];
            $BalanceQueryResult->balance = $parseArray['balance'];

        } catch (\Exception $e) {
            $BalanceQueryResult->code = SubmitResult::CODE_FAIL;
            $BalanceQueryResult->message = $e->getMessage();
        }

        return $BalanceQueryResult;
    }


}