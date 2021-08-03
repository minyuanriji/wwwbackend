<?php

namespace app\controllers\api;


use app\forms\api\AppVersionForm;

class AppController extends ApiController {

    /**
     * 获取版本信息
     * @return yii\web\Response
     * @throws \Exception
     */
    public function actionVersion(){
        $form = new AppVersionForm();
        $form->attributes = $this->requestData;
        return $this->asJson($form->getVersion());
    }

}