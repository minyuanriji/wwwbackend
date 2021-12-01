<?php

namespace app\plugins\taolijin\controllers;

use app\models\User;
use app\plugins\taolijin\forms\common\AliAccForm;
use app\plugins\taolijin\models\TaolijinAli;
use app\plugins\taolijin\models\TaolijinUserAuth;
use lin010\taolijin\Ali;
use yii\web\Controller;

class AuthController extends Controller{

    public function actionAliAuth(){

        $userId = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;
        $aliId  = isset($_GET['ali_id']) ? intval($_GET['ali_id']) : 0;

        $state = uniqid();

        $cache = \Yii::$app->getCache();
        $cacheKey = "AuthController::actionAliAuth:{$state}";

        if(!empty($_GET['code']) && !empty($_GET['state'])){
            $cacheKey = "AuthController::actionAliAuth:" . $_GET['state'];
            $data = $cache->get($cacheKey);
            if($data){
                $cache->set($cacheKey, null);
                $aliModel = TaolijinAli::findOne([
                    "id"       => isset($data['ali_id']) ? $data['ali_id'] : 0,
                    "ali_type" => "ali"
                ]);
                if(!$aliModel || $aliModel->is_delete){
                    die("联盟[ID:{$aliId}]不存在");
                }

                $acc = AliAccForm::getByModel($aliModel);
                $ali = new Ali($acc->app_key, $acc->secret_key);
                $res = $ali->auth->getToken([
                    "code" => $_GET['code']
                ]);
                !empty($res->code) && die($res->msg);

                $tokenData = $res->getTokenData();

                $user = User::findOne(isset($data['user_id']) ? intval($data['user_id']) : 0);
                if($user){
                    $userAuth = TaolijinUserAuth::findOne([
                        "ali_id"  => $aliModel->id,
                        "user_id" => $user->id
                    ]);
                    if(!$userAuth){
                        $userAuth = new TaolijinUserAuth([
                            "mall_id"    => $aliModel->mall_id,
                            "ali_id"     => $aliModel->id,
                            "user_id"    => $user->id,
                            "created_at" => time()
                        ]);
                        $extraData = json_encode([
                            "taobao_user_nick" => isset($tokenData['taobao_user_nick']) ? $tokenData['taobao_user_nick'] : "",
                            "token_type"       => isset($tokenData['token_type']) ? $tokenData['token_type'] : "",
                            "taobao_open_uid"  => isset($tokenData['taobao_open_uid']) ? $tokenData['taobao_open_uid'] : "",
                        ]);
                        $userAuth->updated_at              = time();
                        $userAuth->refresh_token_expire_at = intval($tokenData['refresh_token_valid_time']/1000) - 3600 * 24;
                        $userAuth->refresh_token           = $tokenData['refresh_token'];
                        $userAuth->access_token_expire_at  = intval($tokenData['expire_time']/1000);
                        $userAuth->access_token            = $tokenData['access_token'];
                        $userAuth->extra_json_data         = json_encode($extraData);
                        if(!$userAuth->save()){
                            die(json_encode($userAuth->getErrors()));
                        }
                    }
                }

                exit("<div style='text-align:center;margin-top:30px;'>您已成功授权！请稍后...</div>");
            }
        }

        $aliModel = TaolijinAli::findOne([
            "id"       => $aliId,
            "ali_type" => "ali"
        ]);
        if(!$aliModel || $aliModel->is_delete){
            die("联盟[ID:{$aliId}]不存在");
        }

        $setting = @json_decode($aliModel->settings_data, true);
        $hostInfo = \Yii::$app->getRequest()->getHostInfo();
        $redirectUri = "{$hostInfo}/web/index.php?r=plugin/taolijin/auth/ali-auth&ali_id={$aliId}";

        $authUrl  = "https://oauth.m.taobao.com/authorize?response_type=code&client_id=" . $setting['app_key'] . "&redirect_uri={$redirectUri}&state={$state}&view=wap";

        $cache->set($cacheKey, [
            "ali_id"  => $aliId,
            "user_id" => $userId
        ]);

        header("Location: {$authUrl}");
        exit;
    }

}