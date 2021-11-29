<?php
namespace app\controllers;



use app\helpers\tencent_cloud\TencentCloudAudioHelper;

class JobDebugController extends BaseController {

    public function actionIndex(){
        $base64Data = TencentCloudAudioHelper::request("你好");
        print_r($base64Data);
        exit;
    }

}
