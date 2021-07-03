<?php
namespace app\mch\controllers\api;

use app\controllers\api\ApiController;

use app\controllers\api\filters\LoginFilter;
use app\mch\forms\api\CheckoutOrderInfoForm;
use app\mch\forms\api\CheckoutOrderPayForm;
use app\mch\forms\api\CheckoutOrderQrcodeForm;
use Yii;

class CheckoutOrderController extends ApiController{

    public function behaviors(){
        return array_merge(parent::behaviors(), [
            'login' => [
                'class' => LoginFilter::class,
            ]
        ]);
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
}