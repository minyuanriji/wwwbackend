<?php
namespace app\mch\controllers;

use app\forms\mall\express\ExpressForm;

class ExpressController extends MchController {

    public function actionExpressList(){
        $form = new ExpressForm();
        return $this->asJson($form->getExpressList());
    }

}