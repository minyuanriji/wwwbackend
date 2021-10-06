<?php

namespace app\plugins\addcredit\forms\common;

use app\core\ApiCode;
use app\plugins\addcredit\plateform\sdk\qyj_sdk\PlateForm;

class AccessToken
{
    public static function getAccessToken ($cacheKey, $app_id, $app_key)
    {
        try {
            if (!$cacheKey || !is_string($cacheKey)) {
                throw new \Exception('请传入正确的key！');
            }
            $cacheObject = \Yii::$app->getCache();
            $result = $cacheObject->get($cacheKey);
            if (!$result) {
                $plateForm = new PlateForm();
                $accessToken = $plateForm->getAccessToken($app_id, $app_key);
                if ($accessToken->code) {
                    throw new \Exception($accessToken->message);
                }
                $result = $accessToken->response_content->data->access_token;
                $cacheObject->set($cacheKey, $result, 7200);
            }
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'data' => $result,
            ];
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg'  => $e->getMessage()
            ];
        }
    }
}