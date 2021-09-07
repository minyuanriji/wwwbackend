<?php
namespace app\controllers;

use app\component\efps\Efps;
use app\models\EfpsPaymentOrder;
use app\models\Mall;
use app\models\Order;
use app\models\PaymentOrder;
use app\models\PaymentOrderUnion;
use yii\web\Controller;

class JobDebugController extends BaseController {

    public function actionIndex(){

        $query = Order::find()->alias("o");
        $query->innerJoin(["po" => PaymentOrder::tableName()], "po.order_no=o.order_no");
        $query->innerJoin(["pou" => PaymentOrderUnion::tableName()], "pou.id=po.payment_order_union_id");
        $query->innerJoin(["epo" => EfpsPaymentOrder::tableName()], "epo.payment_order_union_id=pou.id");

        $orders = $query->andWhere([
            "AND",
            ["o.status" => 1],
            ["o.is_pay" => 1],
            "epo.update_at>'1630897200'",
            "(o.total_pay_price+o.express_original_price) > 0"
        ])->asArray()->select(["o.order_no", "epo.outTradeNo"])->all();
        foreach($orders as $order){
            $res = \Yii::$app->efps->payQuery([
                "customerCode" => \Yii::$app->efps->getCustomerCode(),
                "outTradeNo"   => $order['outTradeNo']
            ]);
            if($res['code'] != Efps::CODE_SUCCESS || $res['data']['payState'] != "00"){

            }
        }
        exit;

        /*$this->setWechatParmas(5);
        $wechatModel = \Yii::$app->wechat;
        $res = $wechatModel->app->template_message->send([
            'touser' => 'oHQr7wg8Hf45__91vpq5VksCSK_U',//用户openid
            'template_id' => '0d_ck3gQZprV4A4KEONI8YSoZJ4jDT9Nse0nUnSA_UU',//发送的模板id
            //'url' => 'https://', //发送后用户点击跳转的链接
            'data' => [
                'first' => '您申请的提现金额已到帐',
                'keyword1' => '2015/05/25 14:58',
                'keyword2' => '银行卡转帐',
                'keyword3' => '100.00',
                'keyword4' => '2.00',
                'keyword5' => '98.00',
                'remark' => '感谢你的使用',
            ],
        ]);
        print_r($res);*/
        exit;
    }

}
