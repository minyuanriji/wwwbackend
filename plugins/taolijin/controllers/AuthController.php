<?php

namespace app\plugins\taolijin\controllers;

use app\plugins\taolijin\models\TaolijinAli;
use yii\web\Controller;

class AuthController extends Controller{

    public function actionAliAuth($ali_id){
        $aliModel = TaolijinAli::findOne([
            "id"       => $ali_id,
            "ali_type" => "ali"
        ]);
        if(!$aliModel || $aliModel->is_delete){
            throw new \Exception("联盟[ID:{$ali_id}]不存在");
        }

        $setting = @json_decode($aliModel->settings_data, true);

        $hostInfo = \Yii::$app->getRequest()->getHostInfo();
        $hostInfo = "https://dev.mingyuanriji.cn";
        $redirectUri = "{$hostInfo}/web/index.php?r=plugin/taolijin/auth/ali-auth&ali_id={$ali_id}";

        $authUrl  = "https://oauth.m.taobao.com/authorize?response_type=code&client_id=" . $setting['app_key'] . "&redirect_uri={$redirectUri}&state=".uniqid()."&view=wap";

        header("Location: {$authUrl}");
        exit;
    }

}