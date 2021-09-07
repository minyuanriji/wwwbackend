<?php

namespace app\plugins\addcredit\plateform\sdk\kcb_sdk;

use app\core\ApiCode;
use app\plugins\addcredit\forms\common\Request;
use app\plugins\addcredit\plateform\result\BalanceQueryResult;
use app\plugins\addcredit\plateform\result\SubmitResult;
use yii\base\BaseObject;

class AccountBalanceQueryAction extends BaseObject
{
    public $plateforms_params;

    /**
     * 第三方需要参数
     *  userid	    String	    是	账号ID
        sign	    string	    是	签名
     *
        返回结果：字段名	    变量名	    必填	    描述
                id          string      true    userid
                username    string      true    名称
                balance     string      true    余额
     * */
    public function run ()
    {
        $BalanceQueryResult = new BalanceQueryResult();
        try {
            $param = [
                'userid'           => $this->plateforms_params['id'],
                'apikey'           => $this->plateforms_params['secret_key'],
            ];
            ksort($param);
            $sign_str = http_build_query($param);
            $sign = strtoupper(md5($sign_str));
            $param['sign'] = $sign;
            $sign_str .= "&sign=" . $sign;
            $response = Request::http_get(Config::BALANCE_QUERY, $sign_str);
            $parseArray = json_decode($response, true);
            print_r($response);die;
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