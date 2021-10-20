<?php

namespace app\mch\controllers\api;

use app\forms\api\poster\MchPosterForm;
use app\mch\forms\api\CheckoutOrderInfoForm;
use app\mch\forms\api\CheckoutOrderPayForm;
use app\mch\forms\api\CheckoutOrderQrcodeForm;

class CheckoutOrderController extends MchMApiController {

    const action = ['qrcode', 'synthetic-qr-code'];

    public function beforeAction($action){
        /*if($action->id != "qrcode"){
            $this->check_auth = false;
        }*/
        if (!in_array($action->id, self::action)) {
            $this->check_auth = false;
        }
        return parent::beforeAction($action);
    }

    /**
     * 生成结账单
     * @return \yii\web\Response
     * @throws \app\core\exceptions\ClassNotFoundException
     * @throws \yii\base\Exception
     * @throws \yii\db\Exception
     */
    public function actionQrcode(){

        $form = new CheckoutOrderQrcodeForm();
        $form->attributes = $this->requestData;
        $form->mch_id = $this->mch_id;

        return $this->asJson($form->getQrcode());
    }

    /**
     * 生成结账单
     * @return \yii\web\Response
     * @throws \app\core\exceptions\ClassNotFoundException
     * @throws \yii\base\Exception
     * @throws \yii\db\Exception
     */
    public function actionCreate(){
        $form = new CheckoutOrderPayForm();
        $form->attributes = $this->requestData;
        return $this->asJson($form->create());
    }

    /**
     * 结账单支付
     * @return \yii\web\Response
     * @throws \app\core\exceptions\ClassNotFoundException
     * @throws \yii\base\Exception
     * @throws \yii\db\Exception
     */
    public function actionToPay(){
        $form = new CheckoutOrderPayForm();
        $form->attributes = $this->requestData;
        return $this->asJson($form->pay());
    }

    /**
     * 结账单信息1
     * @return \yii\web\Response
     * @throws \app\core\exceptions\ClassNotFoundException
     * @throws \yii\base\Exception
     * @throws \yii\db\Exception
     */
    public function actionInfo(){
        $form = new CheckoutOrderInfoForm();
        $form->attributes = $this->requestData;
        return $this->asJson($form->info());
    }

    /**
     * 生成结账单---二维码(下载二维码)
     * @return
     * @throws \app\core\exceptions\ClassNotFoundException
     * @throws \yii\base\Exception
     * @throws \yii\db\Exception
     */
    public function actionSyntheticQrCode()
    {
        $form = new MchPosterForm();
        $form->attributes = $this->requestData;
        $form->mch_id = $this->mch_id;
        return $this->asJson($form->get());
    }
}