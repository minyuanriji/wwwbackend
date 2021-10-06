<?php

namespace app\plugins\addcredit\plateform\sdk\qyj_sdk;

use app\core\ApiCode;
use app\plugins\addcredit\forms\common\AccessToken;
use app\plugins\addcredit\forms\common\Request;
use app\plugins\addcredit\plateform\result\GoodsDetailResult;
use yii\base\BaseObject;

class GoodsDetailAction extends BaseObject
{
    const GOODS_ID = 97;

    public $AddcreditPlateforms;

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
        $GoodsDetailResult = new GoodsDetailResult();
        try {
            $json_param = json_decode($this->AddcreditPlateforms->json_param, true);
            $param = [
                'app_id'           => $json_param['id'],
                'g_id'             => self::GOODS_ID,
            ];
            ksort($param);
            $paramStr = http_build_query($param);
            $cacheKey = md5($json_param['id'] . $json_param['secret_key']);
            $token = AccessToken::getAccessToken($cacheKey, $json_param['id'], $json_param['secret_key']);
            if (!$token || $token['code'] == ApiCode::CODE_FAIL) {
                throw new \Exception($token['msg'] ?? '获取access_token失败');
            }
            $response = Request::http_get(Config::GET_GOODS_DETAIL, $paramStr, $token['data']);
            $parseArray = json_decode($response, true);
            if (!isset($parseArray['code'])) {
                throw new \Exception("解析数据错误:(接口)" . Config::GET_GOODS_DETAIL);
            }

            if ($parseArray['code'] != Code::OVERALL_SITUATION_SUCCESS) {
                throw new \Exception($parseArray['msg']);
            }

            $GoodsDetailResult->code = GoodsDetailResult::CODE_SUCC;
            $GoodsDetailResult->message = $parseArray['msg'];
            $GoodsDetailResult->request_data = $param;
            $GoodsDetailResult->response_content = $response;

        } catch (\Exception $e) {
            $GoodsDetailResult->code = GoodsDetailResult::CODE_FAIL;
            $GoodsDetailResult->message = $e->getMessage();
        }

        return $GoodsDetailResult;
    }


}