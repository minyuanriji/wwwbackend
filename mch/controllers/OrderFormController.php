<?php
namespace app\mch\controllers;

use app\mch\forms\shop\OrderFormForm;

class OrderFormController extends MchController {

    public function actionAllList(){
        $form = new OrderFormForm();
        return $this->asJson($form->getAllList());
    }
}
