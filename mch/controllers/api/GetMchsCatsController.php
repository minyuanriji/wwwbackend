<?php
namespace app\mch\controllers\api;


use app\controllers\api\ApiController;
use app\mch\forms\api\CommonCatForm;

class GetMchsCatsController extends ApiController {

    public function actionIndex(){

        $form = new CommonCatForm();
        $form->attributes = \Yii::$app->request->get();

        $this->asJson($form->getAll());
    }

}