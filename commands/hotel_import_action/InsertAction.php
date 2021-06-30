<?php
namespace app\commands;

use app\core\ApiCode;
use app\plugins\hotel\forms\mall\HotelImportForm;
use yii\base\Action;

class InsertAction extends Action{

    public $page;
    public $size;
    public $plateform_class;

    public function run(){
        $form = new HotelImportForm();
        $form->page = $this->page;
        $form->plateform_class = $this->plateform_class;
        $form->size = $this->size;
        $res = $form->import();
        if($res['code'] != ApiCode::CODE_SUCCESS){
            echo "[".$this->plateform_class."] Page:" . $this->page . " Failed. ".$res['msg'] ."\n";
        }else{
            echo "[".$this->plateform_class."] Page:" . $this->page . " Finished\n";
        }
    }

}