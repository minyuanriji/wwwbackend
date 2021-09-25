<?php
namespace app\commands;

use app\component\jobs\EfpsPayQueryJob;
use app\component\jobs\SendIntegralJob;
use yii\console\Controller;


class AddQueueTaskController extends Controller {

    public function actionExecute(){

        while (true){
            try{

                $date = date("Y-m-d H:i:s");

                //赠送积分任务
                \Yii::$app->queue->delay(1)->push(new SendIntegralJob());
                //echo "{$date} SendIntegralJob:queue added success\n";

                sleep(5);

            }catch(Exception $e){
                echo $e->getMessage() . "\n";
                \Yii::error('队列任务加入失败,错误信息：'.$e->getMessage());
            }
        }

    }

}