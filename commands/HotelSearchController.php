<?php
namespace app\commands;


use app\plugins\hotel\forms\api\hotel_search\HotelSearchFilterForm;

class HotelSearchController extends BaseCommandController {

    public function actionFilterTask(){
        $pool = new \Swoole\Process\Pool(10);
        $pool->set(['enable_coroutine' => true]);
        $pool->on('WorkerStart', function (\Swoole\Process\Pool $pool, $workerId) {
            echo("[Worker #{$workerId}] WorkerStart, pid: " . posix_getpid() . "\n");
            while(true){
                $cache = \Yii::$app->getCache();
                $cacheKey = "HotelSearchTask";
                $taskData = $cache->get($cacheKey);
                print_r($taskData);
                echo "\n";
                sleep(3);
            }
        });
        $pool->on('WorkerStop', function (\Swoole\Process\Pool $pool, $workerId) {
            echo("[Worker #{$workerId}] WorkerStop\n");
        });
        $pool->start();
    }
}