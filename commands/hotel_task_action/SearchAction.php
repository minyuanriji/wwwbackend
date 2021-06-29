<?php
namespace app\commands\hotel_task_action;


use app\plugins\hotel\forms\api\hotel_search\HotelSearchFilterForm;
use app\plugins\hotel\jobs\HotelSearchFilterJob;
use yii\base\Action;

/**
 * 酒店搜索加速任务
 */
class SearchAction extends Action{

    public function run(){
        while(true){
            $cache = \Yii::$app->getCache();
            $cacheKey = "HotelSearchTask";
            $taskData = $cache->get($cacheKey);
            if(!empty($taskData)){
                $prepareId = array_shift($taskData);
                $form = new HotelSearchFilterForm([
                    "prepare_id" => $prepareId
                ]);
                echo "HotelSearch task:{$prepareId} start\n";
                (new HotelSearchFilterJob([
                    "mall_id" => 5,
                    "form"    => $form
                ]))->execute(null);
                echo "HotelSearch task:{$prepareId} finished\n";
            }
            sleep(1);
        }
    }

}