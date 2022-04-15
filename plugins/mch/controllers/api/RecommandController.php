<?php
namespace app\plugins\mch\controllers\api;

use app\controllers\api\ApiController;
use app\plugins\mch\forms\api\MchRecommandMchDataForm;

class RecommandController extends ApiController {

    public function actionMchData(){
        $form = new MchRecommandMchDataForm();
        $form->attributes = $this->requestData;
        $form->host_info  = \Yii::$app->request->getHostInfo();
        return $this->asJson($form->get());
    }

}