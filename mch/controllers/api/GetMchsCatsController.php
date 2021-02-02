<?php
namespace app\mch\controllers\api;


use app\controllers\api\ApiController;
use app\plugins\mch\forms\mall\CommonCatForm;

class GetMchsCatsController extends ApiController {

    public function actionIndex(){

        $form = new CommonCatForm();
        $form->attributes = \Yii::$app->request->post();

        $this->asJson($form->getList());
    }

}