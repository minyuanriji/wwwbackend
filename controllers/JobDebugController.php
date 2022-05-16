<?php
namespace app\controllers;


use app\logic\IntegralLogic;
use app\models\Mall;
use app\models\Order;
use app\models\Wechat;
use app\plugins\smart_shop\components\AlipaySdkApi;
use app\plugins\smart_shop\components\cyorder_refund_notification\NotificationCyorderRefundWechatJob;
use app\plugins\smart_shop\components\jobs\NotificationCyorderPaidWechatJob;
use app\plugins\smart_shop\components\SmartShop;

class JobDebugController extends BaseController {

    public function actionIndex(){

        \Yii::$app->queue->delay(1)->push(new NotificationCyorderRefundWechatJob([
            "mall_id"  => 5,
            "order_id" => 5033
        ]));
        echo "success";
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
