<?php
namespace app\plugins\income_log\controllers\api;



use app\controllers\api\ApiController;
use app\controllers\api\filters\LoginFilter;
use app\plugins\income_log\forms\api\IncomeDataForm;

class IndexController extends ApiController {

    public function behaviors(){
        return array_merge(parent::behaviors(), [
            'login' => [
                'class' => LoginFilter::class,
            ]
        ]);
    }

    public function actionIncomeData(){
        $form = new IncomeDataForm();
        $form->attributes = $this->requestData;
        return $this->asJson($form->getData());
    }

}