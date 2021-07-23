<?php

namespace app\plugins\addcredit\plateform\sdk\k_default;

use app\controllers\business\WechatTemplate;
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
            /*$post_param = [
                'szAgentId'      => $this->plateforms_params['id'],
                'szVerifyString' => md5("szAgentId=" . $this->plateforms_params['id'] . "&szKey=" . $this->plateforms_params['secret_key']),
                'szFormat'       => "JSON"
            ];*/
            $post_param = [
                'szAgentId'      => 200025,
                'szVerifyString' => md5("szAgentId=200025&szKey=6a299acc10ef41a2b59d031a49ccac68"),
                'szFormat'       => "JSON"
            ];
            /*$wech =  new WechatTemplate();
            $res = $wech->http_get("http://120.25.166.45:10186/plat/api/old/queryBalance?szAgentId=200025&szVerifyString=418649684a0489aa27476ae1cddd84c9&szFormat=JSON");*/
            $response = Request::execute(Config::BALANCE_QUERY, $post_param);
            print_r($response);die;

            $parseArray = @json_decode($response, true);
            if (!isset($parseArray['nRtn'])) {
                throw new \Exception("解析数据错误", ApiCode::CODE_FAIL);
            }

            if ($parseArray['nRtn'] != Code::BALANCE_QUERY_SUCCESS) {
                if (isset($parseArray['szRtnCode'])) {
                    throw new \Exception($parseArray['szRtnCode'] . "---code:" . $parseArray['nRtn'], ApiCode::CODE_FAIL);
                } else {
                    throw new \Exception("未知错误 " . $parseArray['nRtn'], ApiCode::CODE_FAIL);
                }
            }

            $BalanceQueryResult->code = $parseArray['nRtn'];
            $BalanceQueryResult->balance = $parseArray['fBalance'];

        } catch (\Exception $e) {
            $BalanceQueryResult->code = SubmitResult::CODE_FAIL;
            $BalanceQueryResult->message = $e->getMessage();
        }

        return $BalanceQueryResult;
    }


}