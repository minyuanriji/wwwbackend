<?php
namespace app\commands\hotel_import_action;

use app\core\ApiCode;
use app\plugins\hotel\forms\mall\HotelImportForm;
use yii\base\Action;

class InsertAction extends Action{

    public function run($task){
        $form = new HotelImportForm();
        $form->page            = $task['page'];
        $form->plateform_class = $task['plateform_class'];
        $form->size            = $task['size'];
        $res = $form->import();
        if($res['code'] != ApiCode::CODE_SUCCESS){
            echo "[". $form->plateform_class."] Page:".$form->page." Failed. ".$res['msg'] ."\n";
        }else{
            echo "[".$form->plateform_class."] Page:".$form->page." Finished\n";
        }
    }

}