<?php

namespace app\plugins\taolijin\controllers;

use app\plugins\taolijin\forms\common\AliAccForm;
use app\plugins\taolijin\models\TaolijinAli;
use lin010\taolijin\Ali;
use yii\web\Controller;

class AuthController extends Controller{

    public function actionAliAuth($ali_id = 0){

        $state = uniqid();

        $cache = \Yii::$app->getCache();
        $cacheKey = "AuthController::actionAliAuth:{$state}";

        if(!empty($_GET['code']) && !empty($_GET['state'])){
            $cacheKey = "AuthController::actionAliAuth:" . $_GET['state'];
            $data = $cache->get($cacheKey);
            if($data){
                //$cache->set($cacheKey, null);
                $aliModel = TaolijinAli::findOne([
                    "id"       => isset($data['ali_id']) ? $data['ali_id'] : 0,
                    "ali_type" => "ali"
                ]);
                if(!$aliModel || $aliModel->is_delete){
                    die("联盟[ID:{$ali_id}]不存在");
                }

                $acc = AliAccForm::getByModel($aliModel);

                $ali = new Ali($acc->app_key, $acc->secret_key);
                $res = $ali->auth->getToken([
                    "code" => $_GET['code']
                ]);
                print_r($res);
                exit;
            }
        }

        $aliModel = TaolijinAli::findOne([
            "id"       => $ali_id,
            "ali_type" => "ali"
        ]);
        if(!$aliModel || $aliModel->is_delete){
            die("联盟[ID:{$ali_id}]不存在");
        }

        $setting = @json_decode($aliModel->settings_data, true);
        $hostInfo = \Yii::$app->getRequest()->getHostInfo();
        $redirectUri = "{$hostInfo}/web/index.php?r=plugin/taolijin/auth/ali-auth&ali_id={$ali_id}";

        $authUrl  = "https://oauth.m.taobao.com/authorize?response_type=code&client_id=" . $setting['app_key'] . "&redirect_uri={$redirectUri}&state={$state}&view=wap";

        $cache->set($cacheKey, [
            "ali_id" => $ali_id
        ]);

        header("Location: {$authUrl}");
        exit;
    }

}