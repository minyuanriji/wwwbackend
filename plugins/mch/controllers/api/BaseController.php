<?php
namespace app\plugins\mch\controllers\api;


use app\mch\controllers\api\MchMApiController;
use app\plugins\mch\forms\api\MchBaseInfoForm;

class BaseController extends MchMApiController{

    /**
     * 获取基本信息
     * @return \yii\web\Response
     */
    public function actionInfo(){
        $form = new MchBaseInfoForm();
        $form->attributes = $this->requestData;
        $form->mch_id = $this->mch_id;
        return $this->asJson($form->get());
    }

}