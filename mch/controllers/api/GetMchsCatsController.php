<?php
namespace app\mch\controllers\api;


use app\controllers\api\ApiController;
use app\helpers\APICacheHelper;
use app\mch\forms\api\CommonCatForm;

class GetMchsCatsController extends ApiController {

    public function actionIndex(){
        $data = APICacheHelper::get(APICacheHelper::MCH_API_GET_MCHS_CATAS, function($helper){
            $form = new CommonCatForm();
            $form->attributes = \Yii::$app->request->get();
            return $helper($form->getAll());
        });
       return $this->asJson($data);
    }

}