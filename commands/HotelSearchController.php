<?php
namespace app\commands;


use app\models\Mall;
use app\plugins\hotel\forms\api\hotel_search\HotelSearchFilterForm;
use app\plugins\hotel\jobs\HotelSearchFilterJob;

class HotelSearchController extends BaseCommandController {

    public function actionFilterTask(){
        $pool = new \Swoole\Process\Pool(10);
        $pool->set(['enable_coroutine' => true]);
        $pool->on('WorkerStart', function (\Swoole\Process\Pool $pool, $workerId) {
            $mall = Mall::findOne(5);
            print_r($mall);
            exit;
            echo("[Worker #{$workerId}] WorkerStart, pid: " . posix_getpid() . "\n");
            while(true){
                $cache = \Yii::$app->getCache();
                $cacheKey = "HotelSearchTask";
                $taskData = $cache->get($cacheKey);
                if(!empty($taskData)){
                    $prepareId = array_shift($taskData);
                    $form = new HotelSearchFilterForm([
                        "prepare_id" => $prepareId
                    ]);
                    $mall = Mall::findOne(5);
                    print_r($mall);

                    /*(new HotelSearchFilterJob([
                        "mall_id" => 5,
                        "form"    => $form
                    ]))->execute(null);*/
                }
                sleep(1);
            }
        });
        $pool->on('WorkerStop', function (\Swoole\Process\Pool $pool, $workerId) {
            echo("[Worker #{$workerId}] WorkerStop\n");
        });
        $pool->start();
    }
}