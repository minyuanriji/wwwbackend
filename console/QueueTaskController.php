<?php
namespace app\console;

use app\component\jobs\CheckoutOrderDistributionIncomeJob;
use app\component\jobs\EfpsPayQueryJob;
use app\component\jobs\EfpsTransferJob;
use app\component\jobs\OrderDistributionIncomeJob;
use app\component\jobs\SendIntegralJob;
use Yii;


class QueueTaskController extends \yii\console\Controller{

    public function actionExecute(){

        while (true){
            try{

                //交易分账
                \Yii::$app->queue->delay(5)->push(new EfpsTransferJob());

                //支付状态
                \Yii::$app->queue->delay(5)->push(new EfpsPayQueryJob());

                //分佣计划
                \Yii::$app->queue->delay(5)->push(new OrderDistributionIncomeJob());

                //二维码分佣
                \Yii::$app->queue->delay(5)->push(new CheckoutOrderDistributionIncomeJob());

                //赠送积分任务
                \Yii::$app->queue->delay(5)->push(new SendIntegralJob());

            }catch(Exception $e){
                echo $e->getMessage() . "\n";
                Yii::error('任务执行失败,错误信息：'.$e->getMessage());
            }
        }

    }

}