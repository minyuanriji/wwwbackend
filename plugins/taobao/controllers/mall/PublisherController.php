<?php

namespace app\plugins\taobao\controllers\mall;

use app\plugins\Controller;
use app\plugins\taobao\forms\mall\TaobaoPublisherListForm;
use app\plugins\taobao\models\TaobaoAccount;
use lin010\taolijin\Ali;

class PublisherController  extends Controller{

    public static $account_id = 1;

    /**
     * 淘宝客-公用-私域用户备案信息查询
     * @return string|\yii\web\Response
     */
    public function actionIndex(){

        if(!isset($_GET['token']) || empty($_GET['token'])){
            @header("Location: ?r=plugin/taobao/mall/publisher/auth");
            exit;
        }

        if (\Yii::$app->request->isAjax) {
            $form = new TaobaoPublisherListForm();
            $form->attributes = \Yii::$app->request->get();
            $form->account_id = static::$account_id;
            $form->session = $_GET['token'];
            return $this->asJson($form->getList());
        } else {
            return $this->render('index');
        }
    }

    public function actionAuth(){

        try {
            $account = TaobaoAccount::findOne(static::$account_id);
            $state = uniqid();

            if(!empty($_GET['code']) && !empty($_GET['state'])){
                $ali = new Ali($account->app_key, $account->app_secret);
                $res = $ali->auth->getToken([
                    "code" => $_GET['code']
                ]);

                !empty($res->code) && die($res->msg);

                $tokenData = $res->getTokenData();

                @header("Location: ?r=plugin/taobao/mall/publisher/index&token=" . $tokenData['access_token']);
                exit;
            }

            $hostInfo = \Yii::$app->getRequest()->getHostInfo();
            $redirectUri = "{$hostInfo}/web/index.php?r=plugin/taobao/mall/publisher/auth";
            $authUrl  = "https://oauth.m.taobao.com/authorize?response_type=code&client_id=" . $account->app_key . "&redirect_uri={$redirectUri}&state={$state}&view=wap";
            header("Location: {$authUrl}");
            exit;
        }catch (\Exception $e){
            die($e->getMessage());
        }

    }

}