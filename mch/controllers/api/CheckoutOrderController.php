<?php
namespace app\mch\controllers\api;

use app\controllers\api\ApiController;
use app\mch\forms\api\CheckoutOrderSubmitForm;
use Yii;

class CheckoutOrderController extends ApiController{

    /**
     * 生成结账单
     * @return \yii\web\Response
     * @throws \app\core\exceptions\ClassNotFoundException
     * @throws \yii\base\Exception
     * @throws \yii\db\Exception
     */
    public function actionCreate(){
        $form = new CheckoutOrderSubmitForm();
        $form->attributes = $this->requestData;

        return $this->asJson($form->create());

    }

}