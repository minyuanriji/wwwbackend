<?php


namespace app\mch\controllers\api;


use app\controllers\api\ApiController;
use app\forms\api\identity\SmsForm;
use app\mch\forms\api\BindDeviceForm;

class BindDeviceController extends ApiController {

    /**
     * 获取手机验证码
     * @return yii\web\Response
     * @throws \Exception
     */
    public function actionPhoneCode(){
        $smsForm = new SmsForm();
        $smsForm->attributes = $this->requestData;
        return $this->asJson($smsForm->getPhoneCode());
    }

    /**
     * 绑定设备
     * @return yii\web\Response
     * @throws \Exception
     */
    public function actionBindDevice(){
        $form = new BindDeviceForm();
        $form->attributes = $this->requestData;
        return $this->asJson($form->bind());
    }
}