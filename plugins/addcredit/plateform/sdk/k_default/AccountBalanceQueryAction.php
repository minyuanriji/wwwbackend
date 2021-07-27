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
            $response = Request::http_get(Config::BALANCE_QUERY . "?szAgentId=" . $this->plateforms_params['id'] . "&szVerifyString=" . md5("szAgentId=". $this->plateforms_params['id'] . "&szKey=" . $this->plateforms_params['secret_key']) . "&szFormat=JSON");
            $parseArray = @json_decode($response, true);
            if (!isset($parseArray['nRtn'])) {
                throw new \Exception("解析数据错误", ApiCode::CODE_FAIL);
            }

            if ($parseArray['nRtn'] != Code::BALANCE_QUERY_SUCCESS && $parseArray['nRtn'] != Code::Frequent_Operation) {
                if (isset($parseArray['szRtnCode'])) {
                    throw new \Exception($parseArray['szRtnCode'] . "---code:" . $parseArray['nRtn'], ApiCode::CODE_FAIL);
                } else {
                    throw new \Exception("未知错误 " . $parseArray['nRtn'], ApiCode::CODE_FAIL);
                }
            }

            if ($parseArray['nRtn'] == Code::Frequent_Operation) {
                $BalanceQueryResult->balance = '操作频繁，请30秒后再试！';
            } else {
                $BalanceQueryResult->balance = $parseArray['fBalance'] . "元";
            }
            $BalanceQueryResult->code = BalanceQueryResult::CODE_SUCC;

        } catch (\Exception $e) {
            $BalanceQueryResult->code = SubmitResult::CODE_FAIL;
            $BalanceQueryResult->message = $e->getMessage();
        }

        return $BalanceQueryResult;
    }


}