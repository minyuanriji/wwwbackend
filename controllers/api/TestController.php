<?php

namespace app\controllers\api;

use app\forms\common\SmsCommon;
use app\helpers\sms\NewOrderMessage;
use app\helpers\sms\Sms;
use app\logic\AppConfigLogic;
use app\logic\IntegralLogic;
use app\models\ErrorLog;
use app\models\FreeDeliveryRules;
use app\plugins\agent\models\Agent;
use app\plugins\agent\models\AgentLevel;

class TestController extends ApiController
{
    public function actionIndex()
    {
        //短信通知to商家
        $sms       = new Sms();
        $smsConfig = AppConfigLogic::getSmsConfig();
        $sms->sendNewUserMessage($smsConfig['mobile_list'], "x1ang");
    }

    public function actionPassword()
    {

        $a = json_decode('{"integral_num":"300","period":"1","period_unit":"month","expire":"90"}', 1);
var_export($a);

    }

    public function password(){
        echo \Yii::$app->getSecurity()->generatePasswordHash("xuyaoxiang");
    }

    public function actionQueue(){
        $id=\Yii::$app->queue->push(new DemoJob());
        sleep(3);
        $return=\Yii::$app->queue->isDone($id);
        if($return){
            echo "队列已开启";
        }else{
            echo "队列未开启";
        }
    }
}