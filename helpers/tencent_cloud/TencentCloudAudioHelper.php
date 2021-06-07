<?php
namespace app\helpers\tencent_cloud;

use app\forms\common\WebSocketRequestForm;
use TencentCloud\Common\Credential;
use TencentCloud\Common\Exception\TencentCloudSDKException;
use TencentCloud\Common\Profile\ClientProfile;
use TencentCloud\Common\Profile\HttpProfile;
use TencentCloud\Tts\V20190823\Models\TextToVoiceRequest;
use TencentCloud\Tts\V20190823\TtsClient;

class TencentCloudAudioHelper
{
    public static function request($text){

        $secretId  = \Yii::$app->params['tencentCloud']['secret_id'];
        $secretKey = \Yii::$app->params['tencentCloud']['secret_key'];

        try {

            $cred = new Credential($secretId, $secretKey);
            $httpProfile = new HttpProfile();
            $httpProfile->setEndpoint("tts.tencentcloudapi.com");

            $clientProfile = new ClientProfile();
            $clientProfile->setHttpProfile($httpProfile);
            $client = new TtsClient($cred, "ap-guangzhou", $clientProfile);

            $req = new TextToVoiceRequest();

            $params = array(
                "Text"      => $text,
                "SessionId" => md5(uniqid()),
                "ModelType" => 1,
                "Volume"    => 1,
                //"VoiceType" => 1003,
				"Speed"		=> -1
            );
            $req->fromJsonString(json_encode($params));

            $resp = $client->TextToVoice($req);

            return isset($resp->Audio) ? $resp->Audio : null;
        } catch(TencentCloudSDKException $e) {

        }

        return null;
    }
}