<?php
namespace app\controllers;

use app\models\Mall;
use yii\web\Controller;

class JobDebugController extends BaseController {

    public function actionIndex(){
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
