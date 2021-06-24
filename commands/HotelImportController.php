<?php
namespace app\commands;

use app\core\ApiCode;
use app\models\Mall;
use app\plugins\hotel\forms\mall\HotelImportForm;

class HotelImportController extends BaseCommandController
{
    /**
     * 酒店数据同步
     */
    public function actionUpdate(){
        $this->mutiKill(); //只能只有一个维护服务

        \Yii::$app->mall = Mall::findOne(5);

        $page = 1;
        while (true){
            $form = new HotelImportForm();
            $form->page = $page;
            $form->size = 5;
            $form->plateform_class = "app\\plugins\\hotel\\libs\\bestwehotel\\PlateForm";
            $res = $form->import();
            if($res['code'] != ApiCode::CODE_SUCCESS){
                $this->commandOut("error:{$page} for " . $res['msg']);
            }else{
                if($res['data']['next_page'] <= 0){
                    $this->commandOut("finished!");
                    exit;
                }
                $prograss = round(100 * (($page)/$res['data']['total_pages']), 2);
                $this->commandOut("Prograss:{$prograss}%");
            }
            $page += 1;
        }

    }
}