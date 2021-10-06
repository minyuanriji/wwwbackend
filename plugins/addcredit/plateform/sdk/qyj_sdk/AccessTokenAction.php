<?php

namespace app\plugins\addcredit\plateform\sdk\qyj_sdk;

use app\core\ApiCode;
use app\plugins\addcredit\forms\common\Request;
use app\plugins\addcredit\plateform\result\AccessToken;
use app\plugins\addcredit\plateform\result\BalanceQueryResult;
use app\plugins\addcredit\plateform\result\SubmitResult;
use yii\base\BaseObject;

class AccessTokenAction extends BaseObject
{
    public $app_id;
    public $app_key;

    /**
     * 第三方需要参数
     *  app_id	    String	    是
        app_key	    string	    是
     *
        返回结果：字段名	    变量名	    必填	    描述
            {
               "data":{
                    "access_token": "a52e1163-4a22-4084-8d20-17389b56f1e0",
                    "token_type": "bearer",
                    "refresh_token": "076043d0-89a5-43b9-99e6-37716defaf76",
                    "expires_in": 7198,
                    "scope": "all"
               },
               "code": 200,
               "msg": "sucess"
            }
     */
    public function run ()
    {
        $AccessToken = new AccessToken();
        try {
            $param = [
                'app_id'           => $this->app_id,
                'app_key'           => $this->app_key,
            ];
            ksort($param);
            $sign_str = http_build_query($param);
            $response = Request::http_get(Config::GET_ACCESS_TOKEN, $sign_str);
            $parseArray = json_decode($response, true);
            if (!isset($parseArray['code'])) {
                throw new \Exception("解析数据错误:(接口)" . Config::GET_ACCESS_TOKEN);
            }

            if ($parseArray['code'] != Code::OVERALL_SITUATION_SUCCESS) {
                throw new \Exception($parseArray['msg']);
            }

            $AccessToken->code = AccessToken::CODE_SUCC;
            $AccessToken->message = $parseArray['msg'];
            $AccessToken->request_data = $param;
            $AccessToken->response_content = $response;

        } catch (\Exception $e) {
            $AccessToken->code = AccessToken::CODE_FAIL;
            $AccessToken->message = $e->getMessage();
        }

        return $AccessToken;
    }


}