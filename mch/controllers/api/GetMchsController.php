<?php
namespace app\mch\controllers\api;

use app\controllers\api\ApiController;
use app\helpers\APICacheHelper;
use app\mch\forms\common\CommonMchForm;

class GetMchsController extends ApiController {

    /**
     * 获取商户
     * @return \yii\web\Response
     */
    public function actionIndex(){

        //$list = APICacheHelper::get(APICacheHelper::MCH_API_GET_MCHS, function($helper){
            $form = new CommonMchForm();
            $form->attributes = $this->requestData;
            //return $helper($form->getList());
        //});

        return $this->asJson($form->getList());
    }
}