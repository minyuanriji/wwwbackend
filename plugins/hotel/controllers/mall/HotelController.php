<?php
namespace app\plugins\hotel\controllers\mall;

use app\plugins\Controller;
use app\plugins\hotel\forms\mall\HotelImportForm;

class HotelController extends Controller{

    /**
     * 导入数据
     *  @return string|\yii\web\Response
     */
    public function actionImport(){


        $form = new HotelImportForm();
        $form->page = 1495;
        $form->size = 2;
        $form->plateform_class = "app\\plugins\\hotel\\libs\\bestwehotel\\PlateForm";
        print_r($form->import());
        exit;
    }

}