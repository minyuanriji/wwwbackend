<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 视频基础类
 * Author: zal
 * Date: 2020-04-20
 * Time: 10:21
 */

namespace app\forms\common\video;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use yii\base\Component;

abstract class BaseVideo extends Component
{
    abstract public function getVideoUrl($url);

    public function httpGet($url, $param = array(), $headers = array())
    {
        try {
            $url = $this->appendParams($url, $param);
            $client = $this->getClient($headers);
            $response = $client->get($url);
            $body = $response->getBody();
        } catch (ClientException $e) {
            $body = $e->getResponse()->getBody();
        }
        return $body;
    }

    /**
     * 添加参数
     * @param $url
     * @param array $params
     * @return string
     */
    private function appendParams($url, $params = [])
    {
        if (!is_array($params)) {
            return $url;
        }
        if (!count($params)) {
            return $url;
        }
        $url = trim($url, '?');
        $url = trim($url, '&');
        $queryString = $this->paramsToQueryString($params);
        if (mb_stripos($url, '?')) {
            return $url . '&' . $queryString;
        } else {
            return $url . '?' . $queryString;
        }
    }

    /**
     * 数组key值转换url参数格式
     * @param array $params
     * @return string
     */
    private function paramsToQueryString($params = [])
    {
        if (!is_array($params)) {
            return '';
        }
        if (!count($params)) {
            return '';
        }
        $str = '';
        foreach ($params as $k => $v) {
            $v = urlencode($v);
            $str .= "{$k}={$v}&";
        }
        return trim($str, '&');
    }

    /**
     * @param array $headers
     * @return Client
     */
    public function getClient($headers = array())
    {
        return new Client([
            'verify' => false,
            'headers' => $headers
        ]);
    }

    public function httpPost($url, $params = [], $data = [])
    {
        try {
            $url = $this->appendParams($url, $params);
            $response = $this->getClient()->post($url, [
                'form_params' => $data,
            ]);
            $body = $response->getBody();
        } catch (ClientException $exception) {
            $body = $exception->getResponse()->getBody();
        }
        if (!$body) {
            throw new \Exception('x01');
        }
        $res = json_decode($body->getContents(), true);
        if (!$res) {
            throw new \Exception('x02');
        }
        return $res;
    }
}
