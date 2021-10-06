<?php

namespace app\plugins\addcredit\plateform\sdk\qyj_sdk;

use app\core\ApiCode;
use app\plugins\addcredit\forms\common\AccessToken;
use app\plugins\addcredit\forms\common\Request;
use app\plugins\addcredit\plateform\result\CreateOrderResult;
use app\plugins\addcredit\plateform\result\GoodsDetailResult;
use yii\base\BaseObject;

class CreateOrderAction extends BaseObject
{

    public $params;

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
        $CreateOrderResult = new CreateOrderResult();
        try {
            if (isset($this->params['orderInfo']['app_key']) && $this->params['orderInfo']['app_key']) {
                $app_key = $this->params['orderInfo']['app_key'];
                unset($this->params['orderInfo']['app_key']);
            } else {
                throw new \Exception('未获取到app_key！');
            }

            $cacheKey = md5($this->params['orderInfo']['appId'] . $app_key);
            $token = AccessToken::getAccessToken($cacheKey, $this->params['orderInfo']['appId'], $app_key);
            if (!$token || $token['code'] == ApiCode::CODE_FAIL) {
                throw new \Exception($token['msg'] ?? '获取access_token失败');
            }
            $response = Request::execute(Config::CREATE_ORDER, json_encode($this->params), $token, 'application/json');
            $parseArray = json_decode($response, true);
            if (!isset($parseArray['code'])) {
                throw new \Exception("解析数据错误:(接口)" . Config::GET_GOODS_DETAIL);
            }

            if ($parseArray['code'] != Code::OVERALL_SITUATION_SUCCESS) {
                throw new \Exception($parseArray['message']);
            }

            $CreateOrderResult->code = GoodsDetailResult::CODE_SUCC;
            $CreateOrderResult->message = $parseArray['message'];
            $CreateOrderResult->request_data = json_encode($this->params);
            $CreateOrderResult->response_content = $response;

        } catch (\Exception $e) {
            $CreateOrderResult->code = GoodsDetailResult::CODE_FAIL;
            $CreateOrderResult->message = $e->getMessage();
        }

        return $CreateOrderResult;
    }


}