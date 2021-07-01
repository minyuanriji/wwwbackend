<?php
namespace app\commands;

use app\commands\hotel_import_action\InsertAction;
use app\component\lib\LockTools;
use app\models\Mall;
use app\plugins\hotel\models\HotelPlateforms;
use app\plugins\hotel\models\Hotels;

class HotelImportController extends BaseCommandController{

    public function actions(){
        return [
            "insert" => "app\\commands\\hotel_import_action\\InsertAction",
            "update" => "app\\commands\\hotel_import_action\\UpdateAction",
        ];
    }

    public function actionTest(){
        \Yii::$app->mall = Mall::findOne(5);
        $this->runAction("update", [[
            "hotel_id"        => 121436,
            "plateform_code"  => "JJ61383",
            "plateform_class" => "app\\plugins\\hotel\\libs\\bestwehotel\\PlateForm"
        ]]);
        exit;
    }

    /**
     * 酒店数据同步
     */
    public function actionStart(){
        $this->mutiKill(); //只能只有一个维护服务

        $tasks = [];

        /*$demoData = [
            "page_num" => 5000,
            "page_size" => 5,
            "plateform_class" => "app\\plugins\\hotel\\libs\\bestwehotel\\PlateForm"
        ];

        $tasks = [];
        for($i=1; $i <= $demoData['page_num']; $i++){
            $tasks[] = [
                'action'          => 'insert',
                'page'            => $i,
                'size'            => $demoData['page_size'],
                'plateform_class' => $demoData['plateform_class']
            ];
        }*/


        $rows = Hotels::find()->alias("ho")
            ->innerJoin(["p" => HotelPlateforms::tableName()], "p.source_code=ho.id AND p.type='hotel'")
            ->asArray()->where(["ho.price" => 0])->select(["ho.id as hotel_id", "p.plateform_code", "p.plateform_class"])->all();
        foreach($rows as $row){
            $row['action'] = "update";
            $tasks[] = $row;
        }

        $cacheKey = "HotelImportTasks";
        \Yii::$app->getCache()->set($cacheKey, $tasks);

        $lock = new LockTools();
        $lock_name = 'LOCK:HotelImportTasks';
        $lock->unlock($lock_name);

        $pm = new \Swoole\Process\ProcessManager();
        for($i=0; $i <= 50; $i++){
            $pm->add(function (\Swoole\Process\Pool $pool, int $workerId) use($cacheKey){
                $this->commandOut("[Worker #{$workerId}] WorkerStart, pid: " . posix_getpid());
                if(!defined("Yii")){
                    require_once(__DIR__ . '/../vendor/autoload.php');
                    require_once __DIR__ . '/../config/const.php';
                    new \app\core\ConsoleApplication();
                }
                \Yii::$app->mall = Mall::findOne(5);

                while(true){
                    $task = NULL;
                    $lock = new LockTools();
                    $lock_name = 'LOCK:HotelImportTasks';
                    if($lock->lock($lock_name)){
                        $tasks = \Yii::$app->getCache()->get($cacheKey);
                        $task = null;
                        if(!empty($tasks)){
                            $task = array_shift($tasks);
                            \Yii::$app->getCache()->set($cacheKey, $tasks);
                        }
                        $lock->unlock($lock_name);
                    }

                    if(!empty($task)){
                        $this->runAction("insert", [$task]);
                    }
                }
            });
        }
        $pm->start();
    }
}