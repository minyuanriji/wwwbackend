<?php
namespace app\controllers;

use app\forms\common\WebSocketRequestForm;
use TencentCloud\Common\Credential;
use TencentCloud\Common\Exception\TencentCloudSDKException;
use TencentCloud\Common\Profile\ClientProfile;
use TencentCloud\Common\Profile\HttpProfile;
use TencentCloud\Tts\V20190823\Models\TextToVoiceRequest;
use TencentCloud\Tts\V20190823\TtsClient;
use yii\web\Controller;

class JobDebugController extends Controller{

    public function actionIndex(){

        $voiceText = "今天下雨记得带伞";
        $base64Data = $this->requestAudio($voiceText);
        if(!empty($base64Data)){
            $data = [
                "text"       => $voiceText,
                "base64Data" => $base64Data,
                "url"        => ""
            ];

            WebSocketRequestForm::add(new WebSocketRequestForm([
                'action' => 'MchPaidNotify',
                'notify_mobile' => '18818802855',
                'notify_data' => "PAID:" . json_encode($data)
            ]));
        }
    }

    public function requestAudio($text){

        $secretId = "AKIDB8RUWHdxrXv95InwKRIsABPN5Wg4i1a4";
        $secretKey = "OilWBj11i94g1sTx9E3aqonWy93FBpBS";

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
                "VoiceType" => 4
            );
            $req->fromJsonString(json_encode($params));

            $resp = $client->TextToVoice($req);

            return isset($resp->Audio) ? $resp->Audio : null;
        } catch(TencentCloudSDKException $e) {

        }

        return null;
    }
}