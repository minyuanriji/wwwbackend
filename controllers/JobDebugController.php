<?php
namespace app\controllers;


use app\logic\IntegralLogic;
use app\models\Mall;
use app\models\Order;
use app\models\Wechat;
use app\plugins\smart_shop\components\AlipaySdkApi;
use app\plugins\smart_shop\components\SmartShop;

class JobDebugController extends BaseController {

    public function actionIndex(){

        $access_token = "56_bFens-Pp4wK9EXtdRFqJgTn8i7y_Kt7b5OeWhg4_pIpdp0cUffV7Sdth7Bu5-XHWA7rJSXEi6azuaGfGVyJVfSHRdYz4Zx7YCfXE5w1_ASVFPIS1N_Q_9UjpPXNxyt_cmpCVPs93QY90cJgdELGcADDYFA";
        $url = "https://api.weixin.qq.com/wxa/getwxacodeunlimit?access_token=" . $access_token;
        $postData = [
            "page" => "pages/detail/detail",
            "scene" => "id=53",
            "check_path" => true,
            "env_version" => "release"
        ];

        $ch = curl_init(); //用curl发送数据给api
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);

        try {
            $res = @curl_exec($ch);
            print_r($res);exit;
            $error = @curl_error($ch);
            if(!empty($error)){
                throw new \Exception($error);
            }
            @curl_close($ch);
        }catch (\Exception $e){
            echo $e->getMessage();
            exit;
        }

        header('Content-type: image/jpeg');
        echo $res;
        exit;

        /*\Yii::$app->mall = Mall::findOne(5);

        try {
            $shop = new SmartShop();
            $detail = $shop->getOrderDetail("cyorder", "3620");

            $aliPay = new AlipaySdkApi([
                "appId"                  => $shop->setting['ali_sp_appid'],
                "rsaPrivateKeyPath"      => $shop->setting['ali_rsaPrivateKeyPath'],
                "alipayrsaPublicKeyPath" => $shop->setting['ali_alipayrsaPublicKeyPath']
            ]);

            //获取订单详情
            $res = $aliPay->tradeQuery([
                "out_trade_no" => $detail['transaction_id']
            ], $detail['mno_ali']);
            print_r($res);
            exit;
        }catch (\Exception $e){
            echo $e->getMessage() . "\n";
            echo $e->getFile() . "\n";
            echo $e->getLine();
            exit;
        }*/
    }
}
