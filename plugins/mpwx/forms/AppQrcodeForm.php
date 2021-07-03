<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 系统二维码
 * Author: zal
 * Date: 2020-04-14
 * Time: 15:50
 */

namespace app\plugins\mpwx\forms;

use app\core\ApiCode;
use app\helpers\PluginHelper;
use app\models\BaseModel;
use app\plugins\mpwx\Plugin;
use GuzzleHttp\Client;

class AppQrcodeForm extends BaseModel
{
    public function getResponse()
    {
        /** @var Plugin $currentPlugin */
        $currentPlugin = \Yii::$app->plugin->getCurrentPlugin();
        $accessToken = $currentPlugin->getWechat()->getAccessToken();
        $api = "https://api.weixin.qq.com/wxa/getwxacode?access_token={$accessToken}";
        $client = new Client();
        $data = json_encode([
            'path' => 'pages/index/index',
            'width' => 1280,
        ], JSON_UNESCAPED_UNICODE);
        try {
            $response = $client->post($api, [
                'verify' => false,
                'body' => $data,
            ]);
        } catch (\Exception $exception) {
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg' => '与微信服务器连接出错: ' . $exception->getMessage(),
            ];
        }
        $contentTypes = $response->getHeader('Content-Type');
        $body = $response->getBody();
        foreach ($contentTypes as $contentType) {
            if (mb_stripos($contentType, 'image') !== false) {
                $fileName = md5($body) . '.jpg';
                $filePath = PluginHelper::getPluginAssetsPath() . '/temp';
                make_dir($filePath);
                if (file_put_contents($filePath . '/' . $fileName, $body) !== false) {
                    return [
                        'code' => ApiCode::CODE_SUCCESS,
                        'data' => [
                            'qrcode' => PluginHelper::getPluginBaseAssetsUrl() . '/temp/' . $fileName,
                        ],
                    ];
                } else {
                    return [
                        'code' => ApiCode::CODE_FAIL,
                        'msg' => '文件写入失败，请检查目录' . $filePath . '是否有写入权限。',
                    ];
                }
            }
        }
        $result = json_decode((string)$body, true);
        if (isset($result['errcode']) && $result['errcode'] != 0) {
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg' => 'errcode: ' . $result['errcode']
                    . ', errmsg: ' . (isset($result['errmsg']) ? $result['errmsg'] : ''),
            ];
        } else {
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg' => '未知错误: ' . ((string)$body),
            ];
        }
    }
}
