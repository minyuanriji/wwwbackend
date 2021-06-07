<?php
namespace app\helpers\tencent_cloud;


use TencentCloud\Cdb\V20170320\CdbClient;
use TencentCloud\Cdb\V20170320\Models\DescribeBinlogsRequest;
use TencentCloud\Common\Credential;
use TencentCloud\Common\Exception\TencentCloudSDKException;
use TencentCloud\Common\Profile\ClientProfile;
use TencentCloud\Common\Profile\HttpProfile;

class TencentCloudCDBDescribeBinlogs
{
    public static function request()
    {
        $secretId  = \Yii::$app->params['tencentCloud']['secret_id'];
        $secretKey = \Yii::$app->params['tencentCloud']['secret_key'];

        try {

            $cred = new Credential($secretId, $secretKey);
            $httpProfile = new HttpProfile();
            $httpProfile->setEndpoint("cdb.tencentcloudapi.com");

            $clientProfile = new ClientProfile();
            $clientProfile->setHttpProfile($httpProfile);
            $client = new CdbClient($cred, "ap-guangzhou", $clientProfile);

            $req = new DescribeBinlogsRequest();

            $params = array(
                "InstanceId" => "cdb-d8qnp7hd",
                "Offset" => 0,
                "Limit" => 50
            );
            $req->fromJsonString(json_encode($params));

            $resp = $client->DescribeBinlogs($req);

            print_r(json_decode($resp->toJsonString(), true));

        }
        catch(TencentCloudSDKException $e) {
            echo $e;
        }
    }
}