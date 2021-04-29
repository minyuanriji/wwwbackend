<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 公众号模板数据获取
 * Author: zal
 * Date: 2020-04-22
 * Time: 19:20
 */

namespace app\forms\common\mptemplate;

use app\models\BaseModel;
use GuzzleHttp\Client;
use luweiss\Wechat\Wechat;

class MpTplGet extends BaseModel
{
    public $accessToken;

    public function getAccessToken($app_id, $app_secret)
    {
        $wechat = new Wechat([
            'appId' => $app_id,
            'appSecret' => $app_secret,
        ]);
        $this->accessToken = $wechat->getAccessToken();
        return $this;
    }

    public function getTemplateList()
    {
        $api = "https://api.weixin.qq.com/cgi-bin/template/get_all_private_template?access_token={$this->accessToken}";
        $res = $this->get($api);
        return $res['template_list'];
    }

    public function addTemplate($args)
    {
        $api = "https://api.weixin.qq.com/cgi-bin/template/api_add_template?access_token={$this->accessToken}";
        $res = $this->post($api, [
            "template_id_short" => $args
        ]);
        if ($res['errcode'] == 0) {
            return $res['template_id'];
        } else {
            throw new \Exception($res['errmsg']);
        }
    }

    private function getClient()
    {
        return new Client([
            'verify' => false,
        ]);
    }

    private function post($url, $body = array())
    {
        $response = $this->getClient()->post($url, [
            'body' => json_encode($body)
        ]);
        return json_decode($response->getBody(), true);
    }

    private function buildParams($url, $array)
    {
        $query = http_build_query($array);
        $url = trim($url, '?');
        $url = trim($url, '&');
        if (mb_stripos($url, '?')) {
            return $url . '&' . $query;
        } else {
            return $url . '?' . $query;
        }
    }

    private function get($url, $params = array())
    {
        $response = $this->getClient()->get($this->buildParams($url, $params));
        return json_decode($response->getBody(), true);
    }
}
