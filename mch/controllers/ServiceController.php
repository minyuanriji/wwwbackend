<?php
namespace app\mch\controllers;

use app\mch\forms\service\ServiceForm;

class ServiceController extends MchController {

    /**
     * 获取所有可选服务
     * @return \yii\web\Response
     */
    public function actionOptions(){

        $form = new ServiceForm();
        $res = $form->getOptionList();

        return $this->asJson($res);
    }
}