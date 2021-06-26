<?php
namespace app\commands;


use app\models\Mall;
use app\plugins\hotel\forms\api\hotel_search\HotelSearchFilterForm;
use app\plugins\hotel\jobs\HotelSearchFilterJob;

class HotelSearchController extends BaseCommandController {

    public function actionFilterTask(){
        $pool = new \Swoole\Process\Pool(50);
        $pool->set(['enable_coroutine' => true]);
        $pool->on('WorkerStart', function (\Swoole\Process\Pool $pool, $workerId){
            echo("[Worker #{$workerId}] WorkerStart, pid: " . posix_getpid() . "\n");
            if(!defined("Yii")){
                require_once(__DIR__ . '/../vendor/autoload.php');
                require_once __DIR__ . '/../config/const.php';
                $application = new \app\core\ConsoleApplication();
            }
            while(true){
                $cache = \Yii::$app->getCache();
                $cacheKey = "HotelSearchTask";
                $taskData = $cache->get($cacheKey);
                if(!empty($taskData)){
                    $prepareId = array_shift($taskData);
                    $form = new HotelSearchFilterForm([
                        "prepare_id" => $prepareId
                    ]);
                    echo "HotelSearchFilter task start:{$prepareId}\n";
                    (new HotelSearchFilterJob([
                        "mall_id" => 5,
                        "form"    => $form
                    ]))->execute(null);
                    echo "HotelSearchFilter task end\n";
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