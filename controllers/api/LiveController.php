<?php

namespace app\controllers\api;

use app\core\ApiCode;

class LiveController extends ApiController
{
    public function actionGetLive()
    {
        $wechat = \Yii::$app->wechat;
        $accessTokenArray = $wechat->miniProgram->access_token->getToken();
        if (empty($accessTokenArray['access_token'])) {
            $token = (new SetToken())->getToken();
        } else {
            $token = $accessTokenArray['access_token'];
        }

        $live_list_url = 'https://api.weixin.qq.com/wxa/business/getliveinfo?access_token=' . $token;
        $page = 0;  //当前页码
        $rows = 30; //每页记录条数
        $data = array(
            "start" => $page,
            "limit" => $rows
        );
        $data = json_encode($data);
        $res = $this->http_request($live_list_url, $data);
        $result = json_decode($res, true);
        if ($result['errcode'] != ApiCode::CODE_SUCCESS) {
            if ($result['errcode'] == 40001) {
                $token = (new SetToken())->getToken();
                $live_list_url = 'https://api.weixin.qq.com/wxa/business/getliveinfo?access_token=' . $token;
                $page = 0;  //当前页码
                $rows = 30; //每页记录条数
                $data = array(
                    "start" => $page,
                    "limit" => $rows
                );
                $data = json_encode($data);
                $res = $this->http_request($live_list_url, $data);
                $result = json_decode($res, true);
                if ($result['errcode'] != ApiCode::CODE_SUCCESS) {
                    return $this->asJson([
                        'data' => [],
                        'status' => ApiCode::CODE_FAIL,
                        'msg' => 'errcode:' . $result['errcode'] . ', errmsg:' . $result['errmsg']
                    ]);
                }
            } else {
                return $this->asJson([
                    'data' => [],
                    'status' => ApiCode::CODE_FAIL,
                    'msg' => 'errcode:' . $result['errcode'] . ', errmsg:' . $result['errmsg']
                ]);
            }
        }

        $data = [];
        foreach ($result['room_info'] as $key => $val) {
            if ($val['live_status'] == 103) {
                unset($result['room_info'][$key]);
            } else {
                array_push($data, $val);
            }
        }
        return $this->asJson([
            'data' => $data,
            'status' => 1,
            'msg' => 'OK'
        ]);
    }

    function http_request($url, $data = null)
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
        if (!empty($data)) {
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        }
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $output = curl_exec($curl);
        //var_dump(curl_error($curl));
        curl_close($curl);
        return $output;
    }

}











