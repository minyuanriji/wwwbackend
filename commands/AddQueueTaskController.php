<?php
namespace app\commands;

use app\component\jobs\CheckoutOrderDistributionIncomeJob;
use app\component\jobs\EfpsPayQueryJob;
use app\component\jobs\EfpsTransferJob;
use app\component\jobs\OrderDistributionIncomeJob;
use app\component\jobs\SendIntegralJob;
use yii\console\Controller;


class AddQueueTaskController extends Controller {

    public function actionExecute(){

        while (true){
            try{

                $date = date("Y-m-d H:i:s");

                //交易分账
                \Yii::$app->queue->delay(1)->push(new EfpsTransferJob());
                echo "{$date} EfpsTransferJob:queue added success\n";

                //支付状态
                \Yii::$app->queue->delay(1)->push(new EfpsPayQueryJob());
                echo "{$date} EfpsPayQueryJob:queue added success\n";

                //分佣计划
                \Yii::$app->queue->delay(1)->push(new OrderDistributionIncomeJob());
                echo "{$date} OrderDistributionIncomeJob:queue added success\n";

                //二维码分佣
                \Yii::$app->queue->delay(1)->push(new CheckoutOrderDistributionIncomeJob());
                echo "{$date} CheckoutOrderDistributionIncomeJob:queue added success\n";

                //赠送积分任务
                \Yii::$app->queue->delay(1)->push(new SendIntegralJob());
                echo "{$date} SendIntegralJob:queue added success\n";

                sleep(5);

            }catch(Exception $e){
                echo $e->getMessage() . "\n";
                \Yii::error('队列任务加入失败,错误信息：'.$e->getMessage());
            }
        }

    }

}