<?php
namespace app\commands\hotel_import_action;

use app\plugins\hotel\libs\IPlateform;
use app\plugins\hotel\models\HotelPlateforms;
use app\plugins\hotel\models\Hotels;
use yii\base\Action;

class UpdateAction extends Action{

    public function run($task){

        try {
            $plateform = HotelPlateforms::findOne([
                "plateform_class" => $task['plateform_class'],
                "plateform_code"  => $task['plateform_code'],
                "source_code"     => $task['hotel_id']
            ]);
            if(!$plateform){
                throw new \Exception("Hotel ".$task['hotel_id']." Plateform ".$task['plateform_class']." not exists");
            }

            $className = $plateform->plateform_class;
            if(empty($className) || !class_exists($className)){
                throw new \Exception("Plateform Class not exists");
            }
            $classObject = new $className();
            if(!$classObject instanceof IPlateform){
                throw new \Exception("Plateform Class must implements [IPlateform] interface");
            }

            $hotel = Hotels::findOne($task['hotel_id']);
            if(!$hotel){
                throw new \Exception("Hotel ".$task['hotel_id']." not exists");
            }

            $classObject->update($hotel, $plateform);

            echo "Hotel ".$task['hotel_id']." update finished\n";
        }catch (\Exception $e){
            echo "Error:". $e->getMessage() . " File:".$e->getFile()." Line:".$e->getLine()."\n";
        }

    }

}