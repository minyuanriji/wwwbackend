<?php
namespace app\controllers;

use app\forms\common\WebSocketRequestForm;
use yii\web\Controller;

class JobDebugController extends Controller{

    public function actionIndex(){

        WebSocketRequestForm::add(new WebSocketRequestForm([
            'action' => 'MchPaidNotify',
            'notify_mobile' => '13422078495',
            'notify_data' => "PAID:" . json_encode(["text" => "11111", "url" => "https://img-qn.51miz.com/preview/sound/00/27/20/51miz-S272046-BC428C3F.mp3"])
        ]));

    }
}