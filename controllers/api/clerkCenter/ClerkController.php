<?php

namespace app\controllers\api\clerkCenter;

use app\controllers\api\ApiController;
use app\controllers\api\filters\LoginFilter;
use app\forms\api\clerkCenter\ClerkDetailForm;
use app\forms\api\clerkCenter\ClerkDoForm;
use app\forms\api\clerkCenter\ClerkGetLogForm;

class ClerkController extends ApiController{

    public function behaviors(){
        return array_merge(parent::behaviors(), [
            'login' => [
                'class' => LoginFilter::class,
            ]
        ]);
    }

    /**
     * 核销详情
     * @return \yii\web\Response
     */
    public function actionDetail(){
        $form = new ClerkDetailForm();
        $form->attributes = $this->requestData;
        return $this->asJson($form->getDetail());
    }

    /**
     * 统一核销操作
     * @return \yii\web\Response
     */
    public function actionDoClerk(){
        $form = new ClerkDoForm();
        $form->attributes = $this->requestData;
        return $this->asJson($form->doClerk());
    }

    /**
     * 获取核销记录
     * @return \yii\web\Response
     */
    public function actionGetLogs(){
        $form = new ClerkGetLogForm();
        $form->attributes = $this->requestData;
        return $this->asJson($form->getList());
    }

}