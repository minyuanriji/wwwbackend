<?php
namespace app\mch\controllers\api;

use app\controllers\api\ApiController;
use app\controllers\api\filters\LoginFilter;
use app\forms\api\order\ConsumeVerificationInfoForm;

class ConsumeVerificationInfoController extends ApiController{

    public function behaviors(){
        return array_merge(parent::behaviors(), [
            'login' => [
                'class' => LoginFilter::class,
            ]
        ]);
    }

    public function actionUse(){
        $form = new ConsumeVerificationInfoForm();
        $form->attributes = $this->requestData;

        return $this->asJson($form->useConfirm());
    }
}