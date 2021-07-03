<?php
namespace app\component\jobs;


use yii\base\Component;
use yii\queue\JobInterface;
use app\models\Integral;

class SendIntegralJob extends Component implements JobInterface{

    public function execute($queue){
        Integral::sendIntegral();
    }
}