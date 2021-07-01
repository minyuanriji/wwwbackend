<?php
namespace app\commands\hotel_import_action;

use app\core\ApiCode;
use app\plugins\hotel\forms\mall\HotelImportForm;
use yii\base\Action;

class InsertAction extends Action{

    public function run($page, $size, $plateform_class){
        $form = new HotelImportForm();
        $form->page            = $page;
        $form->plateform_class = $plateform_class;
        $form->size            = $size;
        $res = $form->import();
        if($res['code'] != ApiCode::CODE_SUCCESS){
            echo "[{$plateform_class}] Page:{$page} Failed. ".$res['msg'] ."\n";
        }else{
            echo "[{$plateform_class}] Page:{$page} Finished\n";
        }
    }

}