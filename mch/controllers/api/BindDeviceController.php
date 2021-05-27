<?php


namespace app\mch\controllers\api;


use app\controllers\api\ApiController;
use app\forms\api\identity\SmsForm;

class BindDeviceController extends ApiController {

    /**
     * 获取手机验证码
     * @return yii\web\Response
     * @throws \Exception
     */
    public function actionPhoneCode(){
        $smsForom = new SmsForm();
        $smsForom->attributes = $this->requestData;
        return $this->asJson($smsForom->getPhoneCode());
    }

}