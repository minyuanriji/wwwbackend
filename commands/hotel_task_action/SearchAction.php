<?php
namespace app\commands\hotel_task_action;


use app\component\lib\LockTools;
use app\core\ApiCode;
use app\models\Mall;
use app\plugins\hotel\forms\api\hotel_search\HotelSearchDoForm;
use app\plugins\hotel\forms\api\hotel_search\HotelSearchForm;
use app\plugins\hotel\jobs\HotelSearchFilterJob;
use app\plugins\hotel\models\HotelSearch;
use yii\base\Action;

/**
 * 酒店搜索加速任务
 */
class SearchAction extends Action{

    public function run($lock){
        \Yii::$app->mall = Mall::findOne(5);

        while(true){

            $row = HotelSearch::find()->where([
                "is_running" => 1
            ])->select(["search_id"])->orderBy("updated_at ASC")->one();

            if($row){
                $searchId = $row['search_id'];
                $form = new HotelSearchDoForm([
                    "search_id" => $searchId
                ]);
                echo "HotelSearch task:{$searchId} start\n";
                $form->addJob();
                try {
                    $res = $form->run($lock);
                    if($res['code'] != ApiCode::CODE_SUCCESS){
                        echo $res['msg'] . "\n";
                    }else{
                        echo "HotelSearch task:{$searchId} finished. founds ".$res['data']['founds'] . "\n";
                    }
                }catch (\Exception $e){
                    echo $e->getMessage() . "\n";
                }

                $form->removeJob();
            }
            sleep(1);
        }
    }

}