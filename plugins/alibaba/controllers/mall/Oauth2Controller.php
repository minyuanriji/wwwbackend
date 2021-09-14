<?php


namespace app\plugins\alibaba\controllers\mall;

use app\plugins\alibaba\models\AlibabaApp;
use app\plugins\Controller;
use lin010\alibaba\c2b2b\WebOauth2;
use yii\helpers\Url;

class Oauth2Controller extends Controller{

    /**
     * 应用授权刷新凭证
     */
    public function actionRefreshToken(){
        try {
            $app = AlibabaApp::findOne((int)$_GET['id']);
            if(!$app || $app->is_delete){
                throw new \Exception("应用不存在");
            }

            //如果长时凭证已过期，需要重新授权
            if(empty($app->refresh_token) || $app->refresh_expired_at < time()){
                $url = Url::toRoute(["/plugin/alibaba/mall/oauth2/get-token", "id" => $app->id, "from_page" => isset($_GET['from_page']) ? $_GET['from_page'] : null]);
                return $this->redirect($url);
            }

            $auth = new WebOauth2($app->app_key,  $app->secret, \Yii::$app->getRequest()->getAbsoluteUrl());
            $auth->refresh($app->refresh_token);
            if($auth->error){
                throw new \Exception($auth->error);
            }else{
                $tokenInfo = $auth->tokenInfo();

                $app->access_token     = $auth->getToken();
                $app->token_expired_at = abs($tokenInfo['expires_in']);
                $app->updated_at       = time();

                if(!$app->save()){
                    throw new \Exception(json_encode($app->getErrors()));
                }

                if(empty($_GET['from_page']) || $_GET['from_page'] == "app/list"){
                    $url = Url::toRoute(["/plugin/alibaba/mall/app/list", "id" => $app->id]);
                }else{
                    $url = "";
                }

                return $this->redirect($url);
            }

        }catch (\Exception $e){
            die($e->getMessage());
        }
    }

    /**
     * 应用授权获取TOKEN
     */
    public function actionGetToken(){
        try {
            $app = AlibabaApp::findOne((int)$_GET['id']);
            if(!$app || $app->is_delete){
                throw new \Exception("应用不存在");
            }

            $auth = new WebOauth2($app->app_key,  $app->secret, \Yii::$app->getRequest()->getAbsoluteUrl());
            $auth->auth();

            if($auth->error){
                throw new \Exception($auth->error);
            }else{
                $tokenInfo = $auth->tokenInfo();
                if(preg_match("/^(\d{4})(\d{2})(\d{2})(\d{2})(\d{2})(\d{2})/", "20220308113341000+080", $matches)){
                    $refreshExpiredAt = strtotime(sprintf("%s-%s-%s %s:%s:%s", $matches[1], $matches[2], $matches[3], $matches[4], $matches[5], $matches[6]));
                }else{
                    $refreshExpiredAt = abs($tokenInfo['expires_in']);
                }

                $app->access_token       = $auth->getToken();
                $app->token_expired_at   = abs($tokenInfo['expires_in']);
                $app->refresh_token      = $auth->getRefreshToken();
                $app->refresh_expired_at = $refreshExpiredAt - 3600 * 6;
                $app->updated_at         = time();

                if(!$app->save()){
                    throw new \Exception(json_encode($app->getErrors()));
                }

                if(empty($_GET['from_page']) || $_GET['from_page'] == "app/list"){
                    $url = Url::toRoute(["/plugin/alibaba/mall/app/list", "id" => $app->id]);
                }else{
                    $url = "";
                }

                return $this->redirect($url);
            }
        }catch (\Exception $e){
            die($e->getMessage());
        }

    }

}