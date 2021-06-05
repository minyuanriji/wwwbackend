<?php
namespace app\mch\controllers\api;


use app\controllers\api\ApiController;
use app\helpers\APICacheHelper;
use app\mch\forms\api\CommonCatForm;

class GetMchsCatsController extends ApiController {

    public function actionIndex(){
        $form = new CommonCatForm();
        $form->attributes = \Yii::$app->request->get();
       return $this->asJson($form->getAll());
    }

}